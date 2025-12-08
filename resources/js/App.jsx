import React, { useState, useEffect } from "react";
import LoginModal from './components/LoginModal.jsx'
import ErrorOverlay from './components/ErrorOverlay.jsx'
import TaskListApp from './components/TaskListApp-Simple.tsx'

export function App() {
  const [showLogin, setShowLogin] = useState(false);
  const [errorText, setErrorText] = useState(null);

  // Detectar si estamos en la página de tareas
  const isTaskRoute = typeof window !== 'undefined' && 
    (window.location.pathname.includes('/organizer/tareas') || 
     window.location.pathname.includes('/tasks'));

  console.log('App rendered, isTaskRoute:', isTaskRoute);

  useEffect(() => {
    // Solo registrar listeners si estamos en la página de tareas
    if (!isTaskRoute) return;

    // Escuchar evento global de autenticación requerido
    function handleAuthRequired(e) {
      console.warn('Auth requerido:', e.detail)
      setShowLogin(true)
    }

    function handleAppError(e) {
      const msg = e && e.detail && e.detail.message ? e.detail.message : String(e)
      console.error('App error event:', msg)
      setErrorText(msg)
    }

    function handleWindowError(message, source, lineno, colno, error) {
      setErrorText((error && error.stack) || String(message))
    }

    function handleRejection(ev) {
      setErrorText(ev && ev.reason ? String(ev.reason) : 'Unhandled promise rejection')
    }

    window.addEventListener('auth:required', handleAuthRequired)
    window.addEventListener('app:error', handleAppError)
    window.addEventListener('error', handleWindowError)
    window.addEventListener('unhandledrejection', handleRejection)

    return () => {
      window.removeEventListener('auth:required', handleAuthRequired)
      window.removeEventListener('app:error', handleAppError)
      window.removeEventListener('error', handleWindowError)
      window.removeEventListener('unhandledrejection', handleRejection)
    }
  }, [isTaskRoute]);

  // Mostrar TaskListApp si estamos en página de tareas
  if (isTaskRoute) {
    return (
      <>
        <TaskListApp />
        <LoginModal open={showLogin} onClose={() => setShowLogin(false)} />
        <ErrorOverlay error={errorText} onClose={() => setErrorText(null)} />
      </>
    );
  }

  // Para otras páginas, renderizar nada
  return null;
}