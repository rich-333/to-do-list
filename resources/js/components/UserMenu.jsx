import React, { useState } from 'react';

export default function UserMenu() {
  const [isOpen, setIsOpen] = useState(false);
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(false);

  React.useEffect(() => {
    fetchUser();
  }, []);

  const fetchUser = async () => {
    try {
      const res = await fetch('/api/user');
      if (res.ok) {
        const data = await res.json();
        setUser(data);
      }
    } catch (err) {
      console.error('Error fetching user:', err);
    }
  };

  const handleLogout = async () => {
    setLoading(true);
    try {
      const res = await fetch('/logout', { method: 'POST' });
      if (res.ok) {
        window.location.href = '/';
      }
    } catch (err) {
      console.error('Error logging out:', err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{ position: 'relative' }}>
      <button
        onClick={() => setIsOpen(!isOpen)}
        style={{
          background: 'none',
          border: 'none',
          cursor: 'pointer',
          fontSize: '24px',
          padding: '8px',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          borderRadius: '50%',
          transition: 'background-color 0.3s',
          color: '#000'
        }}
        onMouseEnter={(e) => e.target.style.backgroundColor = '#f0f0f0'}
        onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
        title="Menú de usuario"
      >
        👤
      </button>

      {isOpen && (
        <div
          style={{
            position: 'absolute',
            left: 0,
            top: '100%',
            marginTop: '8px',
            background: 'white',
            border: '1px solid #ddd',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            minWidth: '200px',
            zIndex: 1000,
            overflow: 'hidden'
          }}
        >
          {user ? (
            <>
              <div style={{
                padding: '12px 16px',
                borderBottom: '1px solid #f0f0f0',
                backgroundColor: '#f9f9f9'
              }}>
                <div style={{ fontWeight: 'bold', color: '#333', marginBottom: '4px' }}>
                  {user.name || 'Usuario'}
                </div>
                <div style={{ fontSize: '12px', color: '#666' }}>
                  {user.email || ''}
                </div>
              </div>

              <a
                href="/user/profile"
                style={{
                  display: 'block',
                  padding: '12px 16px',
                  color: '#333',
                  textDecoration: 'none',
                  borderBottom: '1px solid #f0f0f0',
                  transition: 'background-color 0.2s',
                  cursor: 'pointer'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f5f5f5'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                ⚙️ Mis datos
              </a>

              <a
                href="/user/settings"
                style={{
                  display: 'block',
                  padding: '12px 16px',
                  color: '#333',
                  textDecoration: 'none',
                  borderBottom: '1px solid #f0f0f0',
                  transition: 'background-color 0.2s',
                  cursor: 'pointer'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f5f5f5'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                ⚙️ Configuración
              </a>

              <button
                onClick={handleLogout}
                disabled={loading}
                style={{
                  width: '100%',
                  padding: '12px 16px',
                  background: 'none',
                  border: 'none',
                  textAlign: 'left',
                  color: '#d32f2f',
                  cursor: loading ? 'not-allowed' : 'pointer',
                  transition: 'background-color 0.2s',
                  fontSize: '14px',
                  fontWeight: '500'
                }}
                onMouseEnter={(e) => !loading && (e.target.style.backgroundColor = '#ffebee')}
                onMouseLeave={(e) => !loading && (e.target.style.backgroundColor = 'transparent')}
              >
                🚪 {loading ? 'Cerrando...' : 'Cerrar sesión'}
              </button>
            </>
          ) : (
            <>
              <a
                href="/login"
                style={{
                  display: 'block',
                  padding: '12px 16px',
                  color: '#0b74de',
                  textDecoration: 'none',
                  borderBottom: '1px solid #f0f0f0',
                  transition: 'background-color 0.2s',
                  cursor: 'pointer',
                  fontWeight: '500'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f5f5f5'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                🔓 Iniciar sesión
              </a>

              <a
                href="/register"
                style={{
                  display: 'block',
                  padding: '12px 16px',
                  color: '#0b74de',
                  textDecoration: 'none',
                  transition: 'background-color 0.2s',
                  cursor: 'pointer',
                  fontWeight: '500'
                }}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f5f5f5'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                ✍️ Registrarse
              </a>
            </>
          )}
        </div>
      )}

      {isOpen && (
        <div
          onClick={() => setIsOpen(false)}
          style={{
            position: 'fixed',
            top: 0,
            left: 0,
            right: 0,
            bottom: 0,
            zIndex: 999
          }}
        />
      )}
    </div>
  );
}
