import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';

interface Task {
    id?: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    fecha_limite?: string;
    etiquetas?: string[];
}

interface TaskFormProps {
    isOpen: boolean;
    task?: Task | null;
    onClose: () => void;
    onSubmit: (task: Omit<Task, 'id'> | Task) => void;
    isLoading?: boolean;
}

const TaskForm: React.FC<TaskFormProps> = ({
    isOpen,
    task,
    onClose,
    onSubmit,
    isLoading = false,
}) => {
    const [formData, setFormData] = useState<Task>({
        titulo: '',
        descripcion: '',
        estado: 'pendiente',
        prioridad: 'media',
        fecha_limite: '',
        etiquetas: [],
    });

    const [newTag, setNewTag] = useState('');

    useEffect(() => {
        if (task) {
            setFormData(task);
        } else {
            setFormData({
                titulo: '',
                descripcion: '',
                estado: 'pendiente',
                prioridad: 'media',
                fecha_limite: '',
                etiquetas: [],
            });
        }
    }, [task, isOpen]);

    const handleChange = (
        e: React.ChangeEvent<
            HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
        >
    ) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };

    const handleAddTag = () => {
        if (newTag.trim()) {
            setFormData(prev => ({
                ...prev,
                etiquetas: [...(prev.etiquetas || []), newTag.trim()],
            }));
            setNewTag('');
        }
    };

    const handleRemoveTag = (index: number) => {
        setFormData(prev => ({
            ...prev,
            etiquetas: (prev.etiquetas || []).filter((_, i) => i !== index),
        }));
    };

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (formData.titulo.trim()) {
            onSubmit(formData);
        }
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4">
                {/* Header */}
                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 className="text-xl font-bold text-gray-900 dark:text-white">
                        {task?.id ? 'Editar Tarea' : 'Nueva Tarea'}
                    </h2>
                    <button
                        onClick={onClose}
                        className="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
                    >
                        <X size={24} />
                    </button>
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit} className="p-6 space-y-4">
                    {/* Título */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Título *
                        </label>
                        <input
                            type="text"
                            name="titulo"
                            value={formData.titulo}
                            onChange={handleChange}
                            placeholder="Ej: Comprar leche"
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500"
                            required
                        />
                    </div>

                    {/* Descripción */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Descripción
                        </label>
                        <textarea
                            name="descripcion"
                            value={formData.descripcion || ''}
                            onChange={handleChange}
                            placeholder="Detalles de la tarea..."
                            rows={3}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500"
                        />
                    </div>

                    {/* Estado */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Estado
                        </label>
                        <select
                            name="estado"
                            value={formData.estado}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="pendiente">Pendiente</option>
                            <option value="en_progreso">En Progreso</option>
                            <option value="completada">Completada</option>
                        </select>
                    </div>

                    {/* Prioridad */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Prioridad
                        </label>
                        <select
                            name="prioridad"
                            value={formData.prioridad || 'media'}
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        >
                            <option value="baja">Baja</option>
                            <option value="media">Media</option>
                            <option value="alta">Alta</option>
                        </select>
                    </div>

                    {/* Fecha Límite */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Fecha Límite
                        </label>
                        <input
                            type="date"
                            name="fecha_limite"
                            value={
                                formData.fecha_limite
                                    ? new Date(
                                          formData.fecha_limite
                                      ).toISOString().split('T')[0]
                                    : ''
                            }
                            onChange={handleChange}
                            className="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                        />
                    </div>

                    {/* Tags */}
                    <div>
                        <label className="block text-sm font-medium text-gray-900 dark:text-white mb-1">
                            Etiquetas
                        </label>
                        <div className="flex gap-2 mb-2">
                            <input
                                type="text"
                                value={newTag}
                                onChange={e => setNewTag(e.target.value)}
                                placeholder="Agregar etiqueta"
                                className="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500"
                                onKeyPress={e => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        handleAddTag();
                                    }
                                }}
                            />
                            <button
                                type="button"
                                onClick={handleAddTag}
                                className="px-3 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                            >
                                Agregar
                            </button>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {(formData.etiquetas || []).map((tag, idx) => (
                                <div
                                    key={idx}
                                    className="flex items-center gap-2 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full"
                                >
                                    <span>{tag}</span>
                                    <button
                                        type="button"
                                        onClick={() => handleRemoveTag(idx)}
                                        className="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200"
                                    >
                                        <X size={16} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Buttons */}
                    <div className="flex gap-2 pt-4">
                        <button
                            type="button"
                            onClick={onClose}
                            className="flex-1 px-4 py-2 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            disabled={isLoading || !formData.titulo.trim()}
                            className="flex-1 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                        >
                            {isLoading ? 'Guardando...' : 'Guardar'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
};

export default TaskForm;
