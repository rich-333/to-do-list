import React, { useState, useEffect } from "react";
import TaskListApp from './components/TaskListApp-Simple.tsx';

export default function RootApp() {
  const isTaskRoute = typeof window !== 'undefined' && 
    (window.location.pathname.includes('/organizer/tareas') || 
     window.location.pathname.includes('/tasks'));

  console.log('RootApp mounted, isTaskRoute:', isTaskRoute);

  if (isTaskRoute) {
    return <TaskListApp />;
  }

  return null;
}
