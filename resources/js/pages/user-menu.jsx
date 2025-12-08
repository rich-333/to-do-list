import React from 'react';
import ReactDOM from 'react-dom/client';
import UserMenu from '../components/UserMenu.jsx';

// Montar el componente UserMenu en el contenedor
const userMenuRoot = document.getElementById('user-menu-root');
if (userMenuRoot) {
  ReactDOM.createRoot(userMenuRoot).render(<UserMenu />);
}
