import React from "react";
import ReactDOM from "react-dom/client";
import TaskPage from "./pages/TaskPage.jsx";
import '../css/app.css'

console.log('=== Main.jsx loading ===');
const root = document.getElementById('root');
console.log('Root element found:', !!root);

if (root) {
  console.log('Rendering TaskPage...');
  ReactDOM.createRoot(root).render(
    <React.StrictMode>
      <TaskPage />
    </React.StrictMode>
  );
} else {
  console.error('No #root element found');
}
