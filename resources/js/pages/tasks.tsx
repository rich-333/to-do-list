import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import TaskList from '@/components/TaskList';
import TaskForm from '@/components/TaskForm';
import { index as tasksRoute } from '@/routes/tasks';
import { type BreadcrumbItem } from '@/types';
import {
    getTasks,
    deleteTask,
    toggleTaskStatus,
    createTask,
    updateTask,
} from '@/api/Tasks';

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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
    {
        title: 'Tareas',
        href: tasksRoute.url(),
    },
];

interface Props {
    layout?: boolean;
}

export default function Tasks({ layout = true }: Props) {
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
            const data = await getTasks();
            setTasks(Array.isArray(data) ? data : data.data || []);
        } catch (err) {
            console.error('Error loading tasks:', err);
            setError('Error al cargar las tareas');
        } finally {
            setLoading(false);
        }
    };

    const handleTaskToggle = async (taskId: number) => {
        const task = tasks.find(t => t.id === taskId);
        if (!task) return;

        const newStatus =
            task.estado === 'completada' ? 'pendiente' : 'completada';

        try {
            await toggleTaskStatus(taskId, newStatus);
            setTasks(
                tasks.map(t =>
                    t.id === taskId ? { ...t, estado: newStatus as any } : t
                )
            );
        } catch (err) {
            console.error('Error toggling task:', err);
        }
    };

    const handleTaskDelete = async (taskId: number) => {
        if (!confirm('¿Estás seguro de que deseas eliminar esta tarea?')) {
            return;
        }

        try {
            await deleteTask(taskId);
            setTasks(tasks.filter(t => t.id !== taskId));
        } catch (err) {
            console.error('Error deleting task:', err);
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

    const handleFormSubmit = async (formData: Omit<Task, 'id'> | Task) => {
        try {
            setIsSubmitting(true);
            if (editingTask && editingTask.id) {
                // Editar tarea existente
                const updatedTask = await updateTask(editingTask.id, formData);
                setTasks(
                    tasks.map(t =>
                        t.id === editingTask.id ? updatedTask : t
                    )
                );
            } else {
                // Crear nueva tarea
                const newTask = await createTask(formData as Task);
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
            <>
                <Head title="Tareas" />
                <div className="flex items-center justify-center h-screen">
                    <p className="text-gray-600">Cargando tareas...</p>
                </div>
            </>
        );
    }

    // Contenido principal sin layout
    const content = (
        <>
            <Head title="Tareas" />
            {error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {error}
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
        </>
    );

    // Si tiene layout, envolver con AppLayout
    if (layout) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <div className="flex-1 overflow-auto p-6">
                    {content}
                </div>
            </AppLayout>
        );
    }

    // Si no, mostrar sin layout
    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-950 p-6">
            {content}
        </div>
    );
}
