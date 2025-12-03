import React, { useState, useEffect } from 'react';
import TaskList from './TaskList';
import TaskForm from './TaskForm';

interface Task {
    id: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    fecha_limite?: string;
    subtareas?: any[];
    etiquetas?: string[];
}

interface FormTask {
    id?: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    fecha_limite?: string;
    etiquetas?: string[];
}

const TaskListApp: React.FC = () => {
    const [tasks, setTasks] = useState<Task[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);
    const [editingTask, setEditingTask] = useState<Task | null>(null);
    const [isSubmitting, setIsSubmitting] = useState(false);

    useEffect(() => {
        loadTasks();
    }, []);

    const loadTasks = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/v1/tasks');
            if (!response.ok) throw new Error('Error cargando tareas');
            
            const data = await response.json();
            const tasksList = Array.isArray(data) ? data : data.data || [];
            setTasks(tasksList);
        } catch (err) {
            console.error('Error loading tasks:', err);
            setError('Error al cargar las tareas');
            setTasks([]);
        } finally {
            setLoading(false);
        }
    };

    const handleTaskToggle = async (taskId: number) => {
        const task = tasks.find(t => t.id === taskId);
        if (!task) return;

        const newStatus = task.estado === 'completada' ? 'pendiente' : 'completada';

        try {
            const response = await fetch(`/api/v1/tasks/${taskId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ estado: newStatus })
            });

            if (!response.ok) throw new Error('Error actualizando tarea');

            setTasks(
                tasks.map(t =>
                    t.id === taskId ? { ...t, estado: newStatus as any } : t
                )
            );
        } catch (err) {
            console.error('Error toggling task:', err);
            setError('Error al actualizar la tarea');
        }
    };

    const handleTaskDelete = async (taskId: number) => {
        if (!confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
            return;
        }

        try {
            const response = await fetch(`/api/v1/tasks/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });

            if (!response.ok) throw new Error('Error eliminando tarea');

            setTasks(tasks.filter(t => t.id !== taskId));
        } catch (err) {
            console.error('Error deleting task:', err);
            setError('Error al eliminar la tarea');
        }
    };

    const handleTaskEdit = (task: Task) => {
        setEditingTask(task);
        setIsFormOpen(true);
    };

    const handleAddTask = () => {
        setEditingTask(null);
        setIsFormOpen(true);
    };

    const handleFormClose = () => {
        setIsFormOpen(false);
        setEditingTask(null);
    };

    const handleFormSubmit = async (formData: FormTask) => {
        try {
            setIsSubmitting(true);
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Agregar usuario_id por defecto si no está autenticado
            const payload = {
                ...formData,
                usuario_id: 1,
                estado: formData.estado || 'pendiente'
            };
            
            if (editingTask) {
                // Editar tarea existente
                const response = await fetch(`/api/v1/tasks/${editingTask.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) throw new Error('Error actualizando tarea');

                const updatedTask = await response.json();
                setTasks(
                    tasks.map(t =>
                        t.id === editingTask.id ? updatedTask : t
                    )
                );
            } else {
                // Crear nueva tarea
                const response = await fetch('/api/v1/tasks', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                });

                if (!response.ok) throw new Error('Error creando tarea');

                const newTask = await response.json();
                setTasks([newTask, ...tasks]);
            }
            handleFormClose();
        } catch (err) {
            console.error('Error submitting form:', err);
            setError('Error al guardar la tarea');
        } finally {
            setIsSubmitting(false);
        }
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center h-screen bg-gray-100">
                <p className="text-gray-600">Cargando tareas...</p>
            </div>
        );
    }

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-950 p-6">
            {error && (
                <div className="mb-4 rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700">
                    {error}
                    <button
                        onClick={() => setError(null)}
                        className="float-right font-bold"
                    >
                        ×
                    </button>
                </div>
            )}
            <TaskList
                tasks={tasks}
                onTaskToggle={handleTaskToggle}
                onTaskDelete={handleTaskDelete}
                onTaskEdit={handleTaskEdit}
                onAddTask={handleAddTask}
            />
            <TaskForm
                isOpen={isFormOpen}
                task={editingTask}
                onClose={handleFormClose}
                onSubmit={handleFormSubmit}
                isLoading={isSubmitting}
            />
        </div>
    );
};

export default TaskListApp;
