import React from 'react'

export default function LoginModal({ open, onClose }) {
  if (!open) return null

  return (
    <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 9999 }}>
      <div style={{ background: '#fff', padding: 24, borderRadius: 8, width: 420, boxShadow: '0 10px 30px rgba(0,0,0,0.2)' }}>
        <h2 style={{ marginTop: 0 }}>Inicia sesión</h2>
        <p>Para ver esta página necesitas iniciar sesión. Puedes iniciar sesión en la app o cerrar este diálogo.</p>
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 16 }}>
          <button onClick={onClose} style={{ padding: '8px 12px' }}>Cerrar</button>
          <a href="/login" style={{ textDecoration: 'none' }}>
            <button style={{ padding: '8px 12px', background: '#2563eb', color: '#fff', border: 'none', borderRadius: 4 }}>Ir a login</button>
          </a>
        </div>
      </div>
    </div>
  )
}
