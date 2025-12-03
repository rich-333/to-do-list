import React, { useState, useEffect, Suspense, lazy } from "react";

// Importar TaskListApp de forma lazy (carga dinámica)
const TaskListApp = lazy(() => import('./components/TaskListApp-Simple.tsx'));

export function App() {
  const [isTaskPage, setIsTaskPage] = useState(false);
  const [showLoginModal, setShowLoginModal] = useState(false);

  useEffect(() => {
    // Detectar si estamos en la página de tareas
    const isTaskRoute = window.location.pathname.includes('/organizer/tareas') || 
                        window.location.pathname.includes('/tasks');
    setIsTaskPage(isTaskRoute);
  }, []);

  // Mostrar TaskListApp si estamos en página de tareas
  if (isTaskPage) {
    return (
      <>
        {showLoginModal && (
          <div style={{
            position: 'fixed',
            top: 0,
            left: 0,
            width: '100%',
            height: '100%',
            backgroundColor: 'rgba(0, 0, 0, 0.5)',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            zIndex: 9999,
            fontFamily: 'system-ui'
          }}>
            <div style={{
              backgroundColor: 'white',
              padding: '40px',
              borderRadius: '8px',
              boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)',
              maxWidth: '400px',
              width: '90%',
              textAlign: 'center'
            }}>
              <h2 style={{ marginTop: 0, color: '#333' }}>Inicia sesión</h2>
              <p style={{ color: '#666', marginBottom: '20px' }}>Necesitas estar autenticado para acceder a las tareas</p>
              <a 
                href="/login" 
                style={{
                  display: 'inline-block',
                  backgroundColor: '#3b82f6',
                  color: 'white',
                  padding: '10px 20px',
                  borderRadius: '4px',
                  textDecoration: 'none',
                  marginRight: '10px'
                }}
              >
                Ir a Login
              </a>
              <button 
                onClick={() => setShowLoginModal(false)}
                style={{
                  backgroundColor: '#e5e7eb',
                  color: '#333',
                  padding: '10px 20px',
                  borderRadius: '4px',
                  border: 'none',
                  cursor: 'pointer'
                }}
              >
                Cerrar
              </button>
            </div>
          </div>
        )}
        <Suspense fallback={
          <div style={{ padding: '20px', fontFamily: 'system-ui' }}>
            <h1>Cargando tareas...</h1>
          </div>
        }>
          <TaskListApp onAuthError={() => setShowLoginModal(true)} />
        </Suspense>
      </>
    );
  }

  // Para otras páginas, renderizar vacío por ahora
  return <div></div>;
}