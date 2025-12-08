// === MODALES (LOGIN, REGISTER, PROFILE) ===

function openLoginModal() {
  renderLoginModal();
}

function closeLoginModal() {
  document.getElementById('login-modal-container').innerHTML = '';
}

function renderLoginModal() {
  const container = document.getElementById('login-modal-container');
  container.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
      <div style="background: white; border-radius: 8px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; margin-bottom: 24px; color: #333;">Iniciar Sesión</h2>
        <form onsubmit="handleLogin(event)">
          <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
            <input type="email" id="login-email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <div style="margin-bottom: 24px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Contraseña</label>
            <input type="password" id="login-password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <button type="submit" style="width: 100%; padding: 10px; background: #0b74de; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer;">Iniciar Sesión</button>
        </form>
        <div style="margin-top: 16px; text-align: center;">
          <p style="margin: 8px 0; font-size: 14px; color: #666;">¿No tienes cuenta? <a href="#" onclick="closeLoginModal(); openRegisterModal();" style="color: #0b74de; text-decoration: none; font-weight: 600;">Regístrate</a></p>
        </div>
        <button onclick="closeLoginModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">✕</button>
      </div>
    </div>
  `;
}

function handleLogin(e) {
  e.preventDefault();
  const email = document.getElementById('login-email').value;
  const password = document.getElementById('login-password').value;
  
  fetch('/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    },
    body: JSON.stringify({ email, password })
  }).then(res => res.json()).then(data => {
    if (data.user) {
      closeLoginModal();
      applyLoggedInState(data.user);
    } else {
      alert(data.message || 'Credenciales inválidas');
    }
  }).catch(err => {
    console.error('Error:', err);
    alert('Error en la conexión al servidor');
  });
}

function openRegisterModal() {
  renderRegisterModal();
}

function closeRegisterModal() {
  document.getElementById('register-modal-container').innerHTML = '';
}

function renderRegisterModal() {
  const container = document.getElementById('register-modal-container');
  container.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
      <div style="background: white; border-radius: 8px; padding: 32px; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; margin-bottom: 24px; color: #333;">Registrarse</h2>
        <form onsubmit="handleRegister(event)">
          <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Nombre</label>
            <input type="text" id="register-name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
            <input type="email" id="register-email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <div style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Contraseña</label>
            <input type="password" id="register-password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <div style="margin-bottom: 24px;">
            <label style="display: block; margin-bottom: 8px; font-weight: 500;">Confirmar Contraseña</label>
            <input type="password" id="register-confirm" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box;" />
          </div>
          <button type="submit" style="width: 100%; padding: 10px; background: #0b74de; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer;">Registrarse</button>
        </form>
        <button onclick="closeRegisterModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">✕</button>
      </div>
    </div>
  `;
}

function handleRegister(e) {
  e.preventDefault();
  const name = document.getElementById('register-name').value;
  const email = document.getElementById('register-email').value;
  const password = document.getElementById('register-password').value;
  const confirm = document.getElementById('register-confirm').value;
  
  if (password !== confirm) {
    alert('Las contraseñas no coinciden');
    return;
  }
  
  fetch('/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    },
    body: JSON.stringify({ name, email, password, password_confirmation: password })
  }).then(res => res.json()).then(data => {
    if (data.user) {
      closeRegisterModal();
      applyLoggedInState(data.user);
    } else {
      console.error('Errores:', data);
      const errorMsg = data.message || 'Error en el registro';
      const errorsDetail = data.errors ? '\n' + Object.entries(data.errors).map(([key, msgs]) => `${key}: ${msgs.join(', ')}`).join('\n') : '';
      alert(errorMsg + errorsDetail);
    }
  }).catch(err => {
    console.error('Error:', err);
    alert('Error en la conexión al servidor');
  });
}

function openProfileModal() {
  renderProfileModal();
}

function closeProfileModal() {
  document.getElementById('profile-modal-container').innerHTML = '';
}

function renderProfileModal() {
  const container = document.getElementById('profile-modal-container');
  fetch('/api/user').then(res => {
    if (!res.ok) throw new Error('No autenticado');
    return res.json();
  }).then(user => {
    container.innerHTML = `
      <div id="profile-modal-overlay" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
        <div style="background: white; border-radius: 8px; padding: 32px; max-width: 480px; width: 95%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative;">
          <h2 style="margin-top: 0; margin-bottom: 16px; color: #333;">Mis Datos</h2>
          <div id="profile-view" style="">
            <div style="margin-bottom: 12px;">
              <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px; font-weight: 600;">Nombre</label>
              <p id="profile-name" style="margin: 0; font-size: 16px;">${user.name}</p>
            </div>
            <div style="margin-bottom: 18px;">
              <label style="display: block; font-size: 12px; color: #666; margin-bottom: 4px; font-weight: 600;">Email</label>
              <p id="profile-email" style="margin: 0; font-size: 16px;">${user.email}</p>
            </div>
            <div style="display:flex; gap:8px;">
              <button id="edit-profile-btn" style="flex:1; padding: 10px; background: #0b74de; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer;">✏️ Editar Datos</button>
              <button onclick="closeProfileModal()" style="padding: 10px; background: #f0f0f0; color: #333; border: none; border-radius: 4px; font-size: 14px; cursor: pointer;">Cerrar</button>
            </div>
          </div>
          <div id="profile-edit" style="display:none; margin-top:12px;">
            <form id="profile-edit-form">
              <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:600;">Nombre</label>
                <input id="edit-name" type="text" value="${user.name}" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" />
              </div>
              <div style="margin-bottom:12px;">
                <label style="display:block; margin-bottom:6px; font-weight:600;">Email</label>
                <input id="edit-email" type="email" value="${user.email}" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:4px;" />
              </div>
              <div id="profile-edit-error" style="color:#c62828; font-size:13px; display:none; margin-bottom:8px;"></div>
              <div style="display:flex; gap:8px;">
                <button id="save-profile-btn" type="button" style="flex:1; padding:10px; background:#0b74de; color:white; border:none; border-radius:4px; cursor:pointer; font-weight:600;">Guardar</button>
                <button id="cancel-edit-btn" type="button" style="padding:10px; background:#f0f0f0; color:#333; border:none; border-radius:4px; cursor:pointer;">Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `;

    // Attach listeners
    const editBtn = document.getElementById('edit-profile-btn');
    const profileView = document.getElementById('profile-view');
    const profileEdit = document.getElementById('profile-edit');
    const saveBtn = document.getElementById('save-profile-btn');
    const cancelBtn = document.getElementById('cancel-edit-btn');
    const errorBox = document.getElementById('profile-edit-error');

    editBtn.addEventListener('click', () => {
      profileView.style.display = 'none';
      profileEdit.style.display = 'block';
      errorBox.style.display = 'none';
      errorBox.textContent = '';
    });

    cancelBtn.addEventListener('click', () => {
      profileEdit.style.display = 'none';
      profileView.style.display = 'block';
    });

    saveBtn.addEventListener('click', async () => {
      const newName = document.getElementById('edit-name').value.trim();
      const newEmail = document.getElementById('edit-email').value.trim();
      errorBox.style.display = 'none';
      errorBox.textContent = '';

      try {
        const res = await fetch('/api/user', {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({ name: newName, email: newEmail })
        });

        const data = await res.json();
        if (res.ok) {
          document.getElementById('profile-name').textContent = data.name;
          document.getElementById('profile-email').textContent = data.email;
          applyLoggedInState(data);
          profileEdit.style.display = 'none';
          profileView.style.display = 'block';
        } else {
          const msg = data.message || 'Error al guardar';
          const details = data.errors ? '\n' + Object.entries(data.errors).map(([k, v]) => `${k}: ${v.join(', ')}`).join('\n') : '';
          errorBox.style.display = 'block';
          errorBox.textContent = msg + details;
        }
      } catch (err) {
        console.error('Error saving profile:', err);
        errorBox.style.display = 'block';
        errorBox.textContent = 'Error en la conexión al servidor';
      }
    });
  }).catch(err => {
    console.error('No autenticado o error:', err);
    alert('Necesitas iniciar sesión para ver este perfil');
    container.innerHTML = '';
  });
}

function openSettingsModal() {
  alert('Configuración en desarrollo');
}
