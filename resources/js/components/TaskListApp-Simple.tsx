import React, { useState, useEffect } from 'react';

const TaskListApp: React.FC = () => {
    const [tasks, setTasks] = useState<any[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        loadTasks();
    }, []);

    const loadTasks = async () => {
        try {
            setLoading(true);
            const response = await fetch('/api/v1/tasks', {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            
            const data = await response.json();
            const tasksList = Array.isArray(data) ? data : data.data || [];
            setTasks(tasksList);
        } catch (err) {
            console.error('Error loading tasks:', err);
            setError(err instanceof Error ? err.message : 'Error al cargar las tareas');
            setTasks([]);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return (
            <div style={{ padding: '20px', fontFamily: 'system-ui', textAlign: 'center' }}>
                <h1>Mi Lista de Tareas</h1>
                <p>Cargando tareas...</p>
            </div>
        );
    }

    return (
        <div style={{ padding: '20px', fontFamily: 'system-ui', maxWidth: '900px', margin: '0 auto' }}>
            <h1>Mi Lista de Tareas</h1>
            
            {error && (
                <div style={{ 
                    color: '#d32f2f', 
                    backgroundColor: '#ffebee', 
                    padding: '10px', 
                    borderRadius: '4px',
                    marginBottom: '20px'
                }}>
                    Error: {error}
                </div>
            )}

            <div style={{ marginBottom: '20px' }}>
                <p><strong>{tasks.length}</strong> tareas total</p>
                <button 
                    onClick={() => alert('Crear nueva tarea - función próximamente')}
                    style={{
                        backgroundColor: '#4CAF50',
                        color: 'white',
                        padding: '10px 20px',
                        border: 'none',
                        borderRadius: '4px',
                        cursor: 'pointer',
                        fontSize: '16px'
                    }}
                >
                    + Nueva Tarea
                </button>
            </div>

            {tasks.length === 0 ? (
                <div style={{
                    backgroundColor: '#f5f5f5',
                    padding: '40px',
                    borderRadius: '4px',
                    textAlign: 'center',
                    color: '#666'
                }}>
                    <p>No hay tareas. ¡Crea una nueva!</p>
                </div>
            ) : (
                <div style={{ display: 'grid', gap: '10px' }}>
                    {tasks.map((task: any) => (
                        <div 
                            key={task.id}
                            style={{
                                backgroundColor: '#fff',
                                padding: '15px',
                                borderLeft: '4px solid #4CAF50',
                                borderRadius: '4px',
                                boxShadow: '0 2px 4px rgba(0,0,0,0.1)'
                            }}
                        >
                            <h3 style={{ margin: '0 0 5px 0' }}>{task.titulo}</h3>
                            {task.descripcion && <p style={{ margin: '0 0 10px 0', color: '#666' }}>{task.descripcion}</p>}
                            <div style={{ fontSize: '12px', color: '#999' }}>
                                <span style={{
                                    display: 'inline-block',
                                    backgroundColor: '#e3f2fd',
                                    padding: '4px 8px',
                                    borderRadius: '3px',
                                    marginRight: '8px'
                                }}>
                                    {task.estado || 'pendiente'}
                                </span>
                                {task.prioridad && <span style={{
                                    display: 'inline-block',
                                    backgroundColor: '#f3e5f5',
                                    padding: '4px 8px',
                                    borderRadius: '3px'
                                }}>
                                    {task.prioridad}
                                </span>}
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default TaskListApp;
