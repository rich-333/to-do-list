import React, { useState, useEffect } from 'react';

export default function UserProfileModal({ isOpen, onClose }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);
  const [editing, setEditing] = useState(false);
  const [formData, setFormData] = useState({
    name: '',
    email: ''
  });
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    if (isOpen) {
      fetchUser();
    }
  }, [isOpen]);

  const fetchUser = async () => {
    try {
      const res = await fetch('/api/user');
      if (res.ok) {
        const data = await res.json();
        setUser(data);
        setFormData({ name: data.name, email: data.email });
      }
    } catch (err) {
      console.error('Error fetching user:', err);
    }
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    setSuccess('');

    try {
      const res = await fetch('/api/user', {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify(formData)
      });

      if (res.ok) {
        setSuccess('Perfil actualizado correctamente');
        setEditing(false);
        await fetchUser();
        setTimeout(() => setSuccess(''), 3000);
      } else {
        setError('Error al actualizar el perfil');
      }
    } catch (err) {
      setError('Error al actualizar el perfil');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  if (!isOpen || !user) return null;

  return (
    <div style={{
      position: 'fixed',
      top: 0,
      left: 0,
      right: 0,
      bottom: 0,
      background: 'rgba(0,0,0,0.5)',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      zIndex: 2000
    }}>
      <div style={{
        background: 'white',
        borderRadius: '8px',
        padding: '32px',
        maxWidth: '400px',
        width: '90%',
        boxShadow: '0 10px 40px rgba(0,0,0,0.2)'
      }}>
        <h2 style={{ marginTop: 0, marginBottom: '24px', color: '#333' }}>Mis Datos</h2>

        {error && (
          <div style={{
            padding: '12px',
            marginBottom: '16px',
            background: '#ffebee',
            color: '#c62828',
            borderRadius: '4px',
            fontSize: '14px'
          }}>
            {error}
          </div>
        )}

        {success && (
          <div style={{
            padding: '12px',
            marginBottom: '16px',
            background: '#e8f5e9',
            color: '#2e7d32',
            borderRadius: '4px',
            fontSize: '14px'
          }}>
            {success}
          </div>
        )}

        {!editing ? (
          <div>
            <div style={{ marginBottom: '16px' }}>
              <label style={{ display: 'block', fontSize: '12px', color: '#666', marginBottom: '4px', fontWeight: '600' }}>
                Nombre
              </label>
              <p style={{ margin: 0, fontSize: '16px' }}>{user.name}</p>
            </div>
            <div style={{ marginBottom: '24px' }}>
              <label style={{ display: 'block', fontSize: '12px', color: '#666', marginBottom: '4px', fontWeight: '600' }}>
                Email
              </label>
              <p style={{ margin: 0, fontSize: '16px' }}>{user.email}</p>
            </div>
            <button
              onClick={() => setEditing(true)}
              style={{
                width: '100%',
                padding: '10px',
                background: '#0b74de',
                color: 'white',
                border: 'none',
                borderRadius: '4px',
                fontSize: '14px',
                fontWeight: '600',
                cursor: 'pointer'
              }}
            >
              ✏️ Editar Datos
            </button>
          </div>
        ) : (
          <form onSubmit={handleSubmit}>
            <div style={{ marginBottom: '16px' }}>
              <label style={{ display: 'block', marginBottom: '8px', fontWeight: '500' }}>
                Nombre
              </label>
              <input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                required
                style={{
                  width: '100%',
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  fontSize: '14px',
                  boxSizing: 'border-box'
                }}
              />
            </div>

            <div style={{ marginBottom: '24px' }}>
              <label style={{ display: 'block', marginBottom: '8px', fontWeight: '500' }}>
                Email
              </label>
              <input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
                style={{
                  width: '100%',
                  padding: '10px',
                  border: '1px solid #ddd',
                  borderRadius: '4px',
                  fontSize: '14px',
                  boxSizing: 'border-box'
                }}
              />
            </div>

            <div style={{ display: 'flex', gap: '12px' }}>
              <button
                type="submit"
                disabled={loading}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#0b74de',
                  color: 'white',
                  border: 'none',
                  borderRadius: '4px',
                  fontSize: '14px',
                  fontWeight: '600',
                  cursor: loading ? 'not-allowed' : 'pointer',
                  opacity: loading ? 0.6 : 1
                }}
              >
                {loading ? 'Guardando...' : 'Guardar Cambios'}
              </button>
              <button
                type="button"
                onClick={() => setEditing(false)}
                style={{
                  flex: 1,
                  padding: '10px',
                  background: '#f0f0f0',
                  color: '#333',
                  border: 'none',
                  borderRadius: '4px',
                  fontSize: '14px',
                  fontWeight: '600',
                  cursor: 'pointer'
                }}
              >
                Cancelar
              </button>
            </div>
          </form>
        )}

        <button
          onClick={onClose}
          style={{
            position: 'absolute',
            top: '16px',
            right: '16px',
            background: 'none',
            border: 'none',
            fontSize: '24px',
            cursor: 'pointer',
            color: '#999'
          }}
        >
          ✕
        </button>
      </div>
    </div>
  );
}
