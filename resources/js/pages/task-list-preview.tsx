import React from 'react';
import TaskList from '@/components/TaskList';

interface Task {
    id: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    etiquetas?: string[];
    subtareas?: Array<{
        id: string;
        title: string;
        completed: boolean;
    }>;
}

// Datos de ejemplo que coinciden con tu imagen
const EXAMPLE_TASKS: Task[] = [
    {
        id: 1,
        titulo: 'Comprar leche',
        descripcion: 'Ir al supermercado',
        estado: 'completada',
        prioridad: 'baja',
        etiquetas: ['Compras', 'Casa'],
        subtareas: [
            { id: '1', title: 'Leche entera', completed: true },
            { id: '2', title: 'Queso', completed: true },
        ],
    },
    {
        id: 2,
        titulo: 'Hacer la tarea de matemáticas',
        descripcion: 'Resolver ejercicios del capítulo 5',
        estado: 'en_progreso',
        prioridad: 'alta',
        etiquetas: ['Escuela'],
        subtareas: [
            { id: '1', title: 'Ejercicio 1-10', completed: true },
            { id: '2', title: 'Ejercicio 11-20', completed: false },
        ],
    },
    {
        id: 3,
        titulo: 'Llamar al dentista',
        descripcion: 'Reservar cita para revisión',
        estado: 'pendiente',
        prioridad: 'media',
        etiquetas: ['Salud'],
    },
    {
        id: 4,
        titulo: 'Limpiar la habitación',
        descripcion: 'Ordenar y aspirar',
        estado: 'pendiente',
        prioridad: 'baja',
        etiquetas: ['Casa'],
        subtareas: [
            { id: '1', title: 'Ordenar ropa', completed: false },
            { id: '2', title: 'Hacer cama', completed: false },
        ],
    },
];

/**
 * Página de ejemplo para visualizar cómo se ve la lista de tareas
 * Reemplaza esto con la página real cuando esté lista
 */
export default function TaskListPreview() {
    const [tasks, setTasks] = React.useState<Task[]>(EXAMPLE_TASKS);

    const handleTaskToggle = (taskId: number) => {
        setTasks(
            tasks.map(t =>
                t.id === taskId
                    ? {
                          ...t,
                          estado: (
                              t.estado === 'completada'
                                  ? 'pendiente'
                                  : 'completada'
                          ) as 'pendiente' | 'en_progreso' | 'completada',
                      }
                    : t
            )
        );
    };

    const handleTaskDelete = (taskId: number) => {
        setTasks(tasks.filter(t => t.id !== taskId));
    };

    const handleTaskEdit = (task: Task) => {
        console.log('Editar tarea:', task);
    };

    const handleAddTask = () => {
        console.log('Agregar nueva tarea');
    };

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-950 p-8">
            <TaskList
                tasks={tasks}
                onTaskToggle={handleTaskToggle}
                onTaskDelete={handleTaskDelete}
                onTaskEdit={handleTaskEdit}
                onAddTask={handleAddTask}
            />
        </div>
    );
}
