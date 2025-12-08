import React from 'react'

export default function ErrorOverlay({ error, onClose }) {
  if (!error) return null

  return (
    <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,0.6)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 10000 }}>
      <div style={{ background: '#fff', padding: 20, borderRadius: 8, maxWidth: 800, width: '90%', boxShadow: '0 8px 30px rgba(0,0,0,0.3)' }}>
        <h3 style={{ marginTop: 0 }}>Error en la aplicación</h3>
        <pre style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', background: '#f5f5f5', padding: 12, borderRadius: 6 }}>{error}</pre>
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 8, marginTop: 12 }}>
          <button onClick={onClose} style={{ padding: '8px 12px' }}>Cerrar</button>
        </div>
      </div>
    </div>
  )
}
