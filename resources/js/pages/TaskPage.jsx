import React, { useState, useEffect } from 'react';

export default function TaskPage() {
  const [tasks, setTasks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [completedTasks, setCompletedTasks] = useState(new Set());

  useEffect(() => {
    fetchTasks();
  }, []);

  const fetchTasks = async () => {
    try {
      const res = await fetch('/api/v1/tasks');
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      setTasks(Array.isArray(data) ? data : data.data || []);
    } catch (err) {
      console.error('Error fetching tasks:', err);
      setTasks([]);
    } finally {
      setLoading(false);
    }
  };

  const toggleTaskCompletion = async (taskId) => {
    const newCompleted = new Set(completedTasks);
    if (newCompleted.has(taskId)) {
      newCompleted.delete(taskId);
    } else {
      newCompleted.add(taskId);
    }
    setCompletedTasks(newCompleted);

    // Actualizar en el servidor
    try {
      await fetch(`/api/v1/tasks/${taskId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ estado: newCompleted.has(taskId) ? 'completada' : 'pendiente' })
      });
    } catch (err) {
      console.error('Error updating task:', err);
    }
  };

  if (loading) {
    return (
      <div style={{ padding: '20px', fontFamily: 'sans-serif' }}>
        <h1>📋 Lista de Tareas</h1>
        <p>Cargando...</p>
      </div>
    );
  }

  return (
    <div style={{ padding: '20px', fontFamily: 'sans-serif', maxWidth: '900px', margin: '0 auto' }}>
      <h1 style={{ textAlign: 'center', color: '#333', marginBottom: '30px' }}>📋 Lista de Tareas</h1>
      
      {tasks.length === 0 ? (
        <p style={{ textAlign: 'center', color: '#999' }}>No hay tareas. ¡Crea una!</p>
      ) : (
        <div>
          <div style={{ marginBottom: '20px', padding: '10px', backgroundColor: '#f0f0f0', borderRadius: '4px' }}>
            <p style={{ margin: 0 }}>
              <strong>Total: {tasks.length} tareas</strong> | 
              <strong style={{ marginLeft: '10px', color: 'green' }}>Completadas: {completedTasks.size}</strong>
            </p>
          </div>

          <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
            {tasks.map((task) => (
              <div
                key={task.id}
                style={{
                  display: 'flex',
                  alignItems: 'flex-start',
                  border: '1px solid #ddd',
                  padding: '15px',
                  borderRadius: '8px',
                  backgroundColor: completedTasks.has(task.id) ? '#e8f5e9' : '#f9f9f9',
                  opacity: completedTasks.has(task.id) ? 0.7 : 1,
                  transition: 'all 0.3s ease'
                }}
              >
                <input
                  type="checkbox"
                  checked={completedTasks.has(task.id)}
                  onChange={() => toggleTaskCompletion(task.id)}
                  style={{
                    marginRight: '12px',
                    marginTop: '4px',
                    cursor: 'pointer',
                    width: '18px',
                    height: '18px'
                  }}
                />
                <div style={{ flex: 1 }}>
                  <h3 style={{ 
                    margin: '0 0 8px 0',
                    textDecoration: completedTasks.has(task.id) ? 'line-through' : 'none',
                    color: completedTasks.has(task.id) ? '#999' : '#333'
                  }}>
                    {task.titulo || 'Sin título'}
                  </h3>
                  {task.descripcion && (
                    <p style={{ 
                      margin: '0 0 8px 0', 
                      color: completedTasks.has(task.id) ? '#aaa' : '#666',
                      textDecoration: completedTasks.has(task.id) ? 'line-through' : 'none'
                    }}>
                      {task.descripcion}
                    </p>
                  )}
                  <div style={{ fontSize: '12px', color: '#999', display: 'flex', gap: '15px' }}>
                    <span>Estado: <strong>{completedTasks.has(task.id) ? '✓ Completada' : 'Pendiente'}</strong></span>
                    {task.prioridad && <span>Prioridad: <strong>{task.prioridad}</strong></span>}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
