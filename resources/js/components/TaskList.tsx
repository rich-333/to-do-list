import React, { useState, useEffect } from 'react';
import { Plus, CheckCircle2, Circle, Trash2, Edit2 } from 'lucide-react';

interface SubTask {
    id: string;
    title: string;
    completed: boolean;
}

interface Task {
    id: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    fecha_limite?: string;
    subtareas?: SubTask[];
    etiquetas?: string[];
}

interface TaskListProps {
    tasks: Task[];
    onTaskToggle: (taskId: number) => void;
    onTaskDelete: (taskId: number) => void;
    onTaskEdit: (task: Task) => void;
    onAddTask: () => void;
}

const TaskList: React.FC<TaskListProps> = ({
    tasks,
    onTaskToggle,
    onTaskDelete,
    onTaskEdit,
    onAddTask,
}) => {
    const [completedCount, setCompletedCount] = useState(0);

    useEffect(() => {
        const completed = tasks.filter(t => t.estado === 'completada').length;
        setCompletedCount(completed);
    }, [tasks]);

    const getPriorityColor = (prioridad?: string) => {
        switch (prioridad) {
            case 'alta':
                return 'border-red-300 bg-red-50 dark:bg-red-950';
            case 'media':
                return 'border-yellow-300 bg-yellow-50 dark:bg-yellow-950';
            case 'baja':
                return 'border-green-300 bg-green-50 dark:bg-green-950';
            default:
                return 'border-gray-300 bg-gray-50 dark:bg-gray-950';
        }
    };

    const getStatusBadge = (estado: string) => {
        const badges: Record<string, { label: string; color: string }> = {
            pendiente: { label: 'Pendiente', color: 'bg-blue-100 text-blue-800' },
            en_progreso: { label: 'En Progreso', color: 'bg-purple-100 text-purple-800' },
            completada: { label: 'Completada', color: 'bg-green-100 text-green-800' },
        };
        const badge = badges[estado] || badges.pendiente;
        return badge;
    };

    return (
        <div className="w-full max-w-2xl mx-auto p-6 bg-white dark:bg-gray-900 rounded-lg shadow-lg">
            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        Mi Lista de Tareas
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400 mt-1">
                        {completedCount} de {tasks.length} completadas
                    </p>
                </div>
                <button
                    onClick={onAddTask}
                    className="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg transition-colors"
                >
                    <Plus size={20} />
                    Nueva Tarea
                </button>
            </div>

            {/* Progress Bar */}
            {tasks.length > 0 && (
                <div className="mb-6">
                    <div className="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Progreso</span>
                        <span>{Math.round((completedCount / tasks.length) * 100)}%</span>
                    </div>
                    <div className="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div
                            className="bg-green-500 h-2 rounded-full transition-all duration-300"
                            style={{
                                width: `${(completedCount / tasks.length) * 100}%`,
                            }}
                        ></div>
                    </div>
                </div>
            )}

            {/* Tasks List */}
            <div className="space-y-3">
                {tasks.length === 0 ? (
                    <div className="text-center py-12">
                        <p className="text-gray-500 dark:text-gray-400">
                            No hay tareas. ¡Crea una nueva!
                        </p>
                    </div>
                ) : (
                    tasks.map((task) => (
                        <div
                            key={task.id}
                            className={`p-4 border-l-4 rounded-lg transition-all ${getPriorityColor(
                                task.prioridad
                            )} ${
                                task.estado === 'completada'
                                    ? 'opacity-60'
                                    : ''
                            }`}
                        >
                            <div className="flex items-start gap-3">
                                {/* Checkbox */}
                                <button
                                    onClick={() => onTaskToggle(task.id)}
                                    className="flex-shrink-0 mt-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                >
                                    {task.estado === 'completada' ? (
                                        <CheckCircle2
                                            size={24}
                                            className="text-green-500"
                                        />
                                    ) : (
                                        <Circle size={24} />
                                    )}
                                </button>

                                {/* Task Content */}
                                <div className="flex-1">
                                    <h3
                                        className={`text-lg font-semibold ${
                                            task.estado === 'completada'
                                                ? 'line-through text-gray-500'
                                                : 'text-gray-900 dark:text-white'
                                        }`}
                                    >
                                        {task.titulo}
                                    </h3>
                                    {task.descripcion && (
                                        <p className="text-gray-700 dark:text-gray-300 mt-1">
                                            {task.descripcion}
                                        </p>
                                    )}

                                    {/* Tags and Status */}
                                    <div className="flex flex-wrap gap-2 mt-2">
                                        <span
                                            className={`text-xs px-2 py-1 rounded-full ${
                                                getStatusBadge(task.estado).color
                                            }`}
                                        >
                                            {getStatusBadge(task.estado).label}
                                        </span>
                                        {task.etiquetas?.map((tag, idx) => (
                                            <span
                                                key={idx}
                                                className="text-xs px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full"
                                            >
                                                {tag}
                                            </span>
                                        ))}
                                    </div>

                                    {/* Subtasks */}
                                    {task.subtareas &&
                                        task.subtareas.length > 0 && (
                                            <div className="mt-3 ml-6 space-y-1">
                                                {task.subtareas.map(
                                                    (subtask) => (
                                                        <div
                                                            key={
                                                                subtask.id
                                                            }
                                                            className="flex items-center gap-2"
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                checked={
                                                                    subtask.completed
                                                                }
                                                                className="w-4 h-4"
                                                                disabled
                                                            />
                                                            <span
                                                                className={
                                                                    subtask.completed
                                                                        ? 'line-through text-gray-500'
                                                                        : 'text-gray-700 dark:text-gray-300'
                                                                }
                                                            >
                                                                {
                                                                    subtask.title
                                                                }
                                                            </span>
                                                        </div>
                                                    )
                                                )}
                                            </div>
                                        )}
                                </div>

                                {/* Actions */}
                                <div className="flex items-center gap-2 flex-shrink-0">
                                    <button
                                        onClick={() => onTaskEdit(task)}
                                        className="p-2 text-blue-500 hover:bg-blue-100 dark:hover:bg-blue-900 rounded transition-colors"
                                        title="Editar tarea"
                                    >
                                        <Edit2 size={18} />
                                    </button>
                                    <button
                                        onClick={() => onTaskDelete(task.id)}
                                        className="p-2 text-red-500 hover:bg-red-100 dark:hover:bg-red-900 rounded transition-colors"
                                        title="Eliminar tarea"
                                    >
                                        <Trash2 size={18} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))
                )}
            </div>
        </div>
    );
};

export default TaskList;
