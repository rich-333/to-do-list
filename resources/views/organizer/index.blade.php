<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OrganizerAI - Debug</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111; margin: 0; padding: 0; }
      .header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; background: #fff; border-bottom: 1px solid #e0e0e0; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
      .header h1 { margin: 0; font-size: 1.5rem; }
      .user-menu-container { display: flex; align-items: center; }
      .container { max-width: 900px; margin: 2.5rem auto; background: #fff; padding: 1.25rem 1.5rem; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.06); }
      a { color:#0b74de; text-decoration:none }
      a:hover { text-decoration:underline }
      ul { padding-left:1rem }
      /* Calendar styles */
      #calendar-root { border: 4px solid #6aa84f; padding: 12px; border-radius: 8px; }
      #cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-top: 8px; }
      /* weekday headers are the first 7 children */
      #cal-grid > div:nth-child(-n+7) { background: #6aa84f; color: #fff; font-weight: 700; padding: 8px 6px; text-align:center; border-radius:6px; }
      /* day cells */
      #cal-grid > div { min-height: 80px; padding: 8px; border-radius: 6px; background: #fff; border: 1px solid #6aa84f; box-sizing: border-box; }
      .cal-day-sat { background: #f4e7c2; }
      .cal-day-sun { background: #e6e6e6; }
      .cal-day-today { background: #fff1b8; border: 2px solid #4a8f3a; }
    </style>
    @vite('resources/js/pages/user-menu.jsx')
  </head>
  <body>
    <div class="header">
      <h1>OrganizerAI — Debug</h1>
      <div class="user-menu-container">
        <button id="user-menu-btn" style="background: none; border: none; cursor: pointer; font-size: 24px; padding: 8px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">👤</button>
      </div>
    </div>
    
    <div id="user-menu-dropdown" style="position: absolute; top: 60px; right: 20px; background: white; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); min-width: 200px; z-index: 1000; display: none; overflow: hidden;">
      <div style="padding: 12px 16px; border-bottom: 1px solid #f0f0f0; background-color: #f9f9f9;">
        <div style="font-weight: bold; color: #333; margin-bottom: 4px;" id="user-name">Usuario</div>
        <div style="font-size: 12px; color: #666;" id="user-email"></div>
      </div>
      <button onclick="openProfileModal()" style="width: 100%; text-align: left; padding: 12px 16px; color: #333; background: none; border: none; border-bottom: 1px solid #f0f0f0; cursor: pointer; font-size: 14px;">⚙️ Mis datos</button>
      <button onclick="openSettingsModal()" style="width: 100%; text-align: left; padding: 12px 16px; color: #333; background: none; border: none; border-bottom: 1px solid #f0f0f0; cursor: pointer; font-size: 14px;">⚙️ Configuración</button>
      <button onclick="openLoginModal()" style="width: 100%; text-align: left; padding: 12px 16px; color: #0b74de; background: none; border: none; border-bottom: 1px solid #f0f0f0; cursor: pointer; font-size: 14px; font-weight: 500;">🔓 Iniciar sesión</button>
      <button id="logout-btn" onclick="logout()" style="width: 100%; padding: 12px 16px; background: none; border: none; text-align: left; color: #d32f2f; cursor: pointer; font-size: 14px; font-weight: 500;">🚪 Cerrar sesión</button>
    </div>
    
    <!-- Modales -->
    <div id="login-modal-container"></div>
    <div id="register-modal-container"></div>
    <div id="profile-modal-container"></div>
    <div id="day-events-modal-container"></div>
    
    <script>
      let loginModalOpen = false;
      let registerModalOpen = false;
      let profileModalOpen = false;
      let isLoggedIn = false;

      const menuBtn = document.getElementById('user-menu-btn');
      const menuDropdown = document.getElementById('user-menu-dropdown');
      
      menuBtn.addEventListener('click', function() {
        menuDropdown.style.display = menuDropdown.style.display === 'none' ? 'block' : 'none';
      });
      
      // Cerrar menú al hacer clic fuera
      document.addEventListener('click', function(e) {
        if (e.target !== menuBtn && !menuDropdown.contains(e.target)) {
          menuDropdown.style.display = 'none';
        }
      });
      
      // Aplicar estado UI cuando hay sesión iniciada
      function applyLoggedInState(user) {
        isLoggedIn = true;
        document.getElementById('user-name').textContent = user.name || 'Usuario';
        document.getElementById('user-email').textContent = user.email || '';
        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) logoutBtn.style.display = 'block';
        document.querySelectorAll('[onclick="openLoginModal()"]').forEach(btn => btn.style.display = 'none');
      }

      // Cargar datos del usuario (inicial)
      async function loadUser() {
        try {
          const res = await fetch('/api/user');
          if (res.ok) {
            const data = await res.json();
            applyLoggedInState(data);
          } else {
                  <?php
                    // Client-side calendar grid: the JS expects an element with id="cal-grid".
                    // We render an empty grid container here; the client script will populate it.
                  ?>

                  <div id="cal-grid" style="display:grid; grid-template-columns: repeat(7, 1fr); gap:6px; margin-top:8px;"></div>
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
            // Cerrar modal y aplicar estado sin recargar
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
            // Registro completado: cerrar modal y aplicar estado autenticado sin recargar
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
                // Update UI
                document.getElementById('profile-name').textContent = data.name;
                document.getElementById('profile-email').textContent = data.email;
                applyLoggedInState(data);
                profileEdit.style.display = 'none';
                profileView.style.display = 'block';
              } else {
                // show validation errors
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
      
      loadUser();
      
      // Agrupar eventos por fecha para modal de día
      const EVENTS_BY_DATE = {};
      @foreach($jsEvents as $ev)
        @php
          if (!empty($ev['inicio'])) {
            $dateKey = (new \DateTime($ev['inicio']))->format('Y-m-d');
            echo "if (!EVENTS_BY_DATE['$dateKey']) EVENTS_BY_DATE['$dateKey'] = [];\n";
            echo "EVENTS_BY_DATE['$dateKey'].push(" . json_encode($ev) . ");\n";
          }
        @endphp
      @endforeach

      function openDayEventsModal(year, month, day) {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const events = EVENTS_BY_DATE[dateStr] || [];
        // Ordenar por hora
        events.sort((a, b) => new Date(a.inicio).getTime() - new Date(b.inicio).getTime());
        
        const container = document.getElementById('day-events-modal-container');
        let eventsHtml = events.map(ev => {
          const startTime = new Date(ev.inicio).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
          const color = ev.color || '#c8e7ff';
          return `
            <div style="padding: 12px; margin-bottom: 8px; border-left: 4px solid ${color}; background: #f9f9f9; border-radius: 4px;">
              <div style="font-weight: 600; color: #333;">${ev.titulo}</div>
              <div style="font-size: 13px; color: #666; margin-top: 4px;">🕒 ${startTime}</div>
              ${ev.descripcion ? `<div style="font-size: 13px; color: #888; margin-top: 4px;">${ev.descripcion}</div>` : ''}
            </div>
          `;
        }).join('');
        
        // Pre-fill datetime with the selected date at 09:00
        const prefilledDateTime = `${dateStr}T09:00`;
        
        container.innerHTML = `
          <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
            <div style="background: white; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
              <h2 style="margin-top: 0; margin-bottom: 16px; color: #333;">Eventos del ${day} de ${month} de ${year}</h2>
              <div style="margin-bottom: 24px; min-height: 100px;">
                ${eventsHtml || '<div style="color: #999; text-align: center; padding: 32px;">No hay eventos este día</div>'}
              </div>
              <button onclick="closeDayEventsModal(); renderQuickAddModal('calendar', '${prefilledDateTime}');" style="width: 100%; padding: 10px; background: #4a8f3a; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">+ Agregar evento</button>
              <button onclick="closeDayEventsModal()" style="width: 100%; padding: 10px; background: #e0e0e0; color: #333; border: none; border-radius: 4px; font-size: 14px; cursor: pointer;">Cerrar</button>
            </div>
          </div>
        `;
      }

      function closeDayEventsModal() {
        document.getElementById('day-events-modal-container').innerHTML = '';
      }
      
      // Pestañas y Quick Add
      let activeTab = 'notes';

      function switchTab(tab) {
        activeTab = tab;
        document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
        document.querySelectorAll('.nav-pill').forEach(p => {
          p.style.background = 'transparent';
          p.style.color = '#111';
          p.style.borderColor = '#111';
        });

        const pane = document.getElementById('tab-' + tab);
        if (pane) pane.style.display = 'block';

        const pill = document.querySelector(`.nav-pill[data-tab="${tab}"]`);
        if (pill) {
          pill.style.background = '#111';
          pill.style.color = '#fff';
          pill.style.borderColor = '#111';
        }
      }

      function onAddClick() {
        renderQuickAddModal(activeTab);
      }

      // color palette for quick-add
      const QA_PASTEL_COLORS = ['#f8c8dc','#ffd8a8','#fff1a8','#c8f0d6','#c8e7ff','#e1c8ff','#f0e1c8','#d8f0ff'];
      let qaSelectedColor = QA_PASTEL_COLORS[0];

      function renderQuickAddModal(tab, prefilledDate = null) {
        const container = document.getElementById('quick-add-modal');
        let title = '';
        let body = '';
        if (tab === 'notes') {
          title = 'Crear Nota rápida';
          body = `
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Título</label><input id="qa-title" type="text" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"/></div>
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Contenido</label><textarea id="qa-content" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" rows="4"></textarea></div>
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Color</label>
              <div id="qa-color-palette" style="display:flex; gap:8px; flex-wrap:wrap;">
                ${QA_PASTEL_COLORS.map(c => `<button type=\"button\" class=\"qa-color-swatch\" data-color=\"${c}\" style=\"width:36px;height:36px;border-radius:6px;border:2px solid transparent;background:${c};cursor:pointer;\"></button>`).join('')}
              </div>
            </div>
          `;
        } else if (tab === 'tasks') {
          title = 'Crear Tarea rápida';
          body = `
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Título</label><input id="qa-title" type="text" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"/></div>
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Descripción</label><textarea id="qa-content" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" rows="3"></textarea></div>
          `;
        } else if (tab === 'calendar') {
          const defaultDateTime = prefilledDate || new Date().toISOString().slice(0, 16);
          title = 'Crear Evento rápido';
          body = `
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Título</label><input id="qa-title" type="text" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"/></div>
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Fecha y hora</label><input id="qa-datetime" type="datetime-local" value="${defaultDateTime}" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;"/></div>
            <div style="margin-bottom:12px;"><label style="display:block;margin-bottom:6px;font-weight:600;">Descripción</label><textarea id="qa-content" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" rows="3"></textarea></div>
          `;
        }

        container.innerHTML = `
          <div style="position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:2100;">
            <div style="background:#fff; border-radius:8px; padding:20px; width: 420px; max-width:94%; position:relative;">
              <h3 style="margin-top:0; margin-bottom:12px;">${title}</h3>
              <div id="qa-body">${body}</div>
              <div style="display:flex; gap:8px; margin-top:12px;">
                <button id="qa-save" style="flex:1; padding:10px; background:#0b74de; color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:600;">Guardar</button>
                <button id="qa-cancel" style="padding:10px; background:#f0f0f0; color:#333; border:none; border-radius:6px; cursor:pointer;">Cancelar</button>
              </div>
              <button id="qa-close" style="position:absolute; top:8px; right:8px; background:none; border:none; font-size:20px; cursor:pointer;">✕</button>
            </div>
          </div>
        `;

        // attach color palette handlers if present
        const palette = document.getElementById('qa-color-palette');
        if (palette) {
          const swatches = Array.from(palette.querySelectorAll('.qa-color-swatch'));
          swatches.forEach(s => {
            const c = s.getAttribute('data-color');
            if (c === qaSelectedColor) {
              s.style.borderColor = '#111';
            }
            s.addEventListener('click', () => {
              qaSelectedColor = c;
              swatches.forEach(x => x.style.borderColor = 'transparent');
              s.style.borderColor = '#111';
            });
          });
        }

        document.getElementById('qa-cancel').addEventListener('click', () => { container.innerHTML = ''; });
        document.getElementById('qa-close').addEventListener('click', () => { container.innerHTML = ''; });
        document.getElementById('qa-save').addEventListener('click', () => { submitQuickAdd(tab); });
      }

      async function submitQuickAdd(tab) {
        const token = document.querySelector('meta[name="csrf-token"]').content || '';
        try {
          let payload = {};
          let url = '';
          if (tab === 'notes') {
            payload.titulo = document.getElementById('qa-title').value || 'Nota rápida';
            payload.contenido = document.getElementById('qa-content').value || '';
            payload.color = qaSelectedColor || null;
            url = '/api/v1/notes';
          } else if (tab === 'tasks') {
            payload.titulo = document.getElementById('qa-title').value || 'Tarea rápida';
            payload.descripcion = document.getElementById('qa-content').value || '';
            url = '/api/v1/tasks';
          } else if (tab === 'calendar') {
            payload.titulo = document.getElementById('qa-title').value || 'Evento rápido';
            const dt = document.getElementById('qa-datetime').value;
            if (dt) payload.inicio = dt;
            payload.descripcion = document.getElementById('qa-content') ? document.getElementById('qa-content').value : '';
            payload.usuario_id = 1;
            url = '/api/v1/events';
          }

          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(payload)
          });

          const data = await res.json();
          if (res.ok) {
            // Close modal
            document.getElementById('quick-add-modal').innerHTML = '';
            // Update UI quickly (append to list)
            if (tab === 'notes') {
              const list = document.getElementById('notes-list');
              const div = document.createElement('a');
              div.href = '/organizer/notas/' + (data.id || '');
              div.style = 'text-decoration:none; color:inherit';
              const borderColor = (data.color || payload.color || '#f7d36c');
              div.innerHTML = `<div class="note-box" style="border:4px solid ${borderColor};"><div class="note-title">${data.titulo || payload.titulo}</div></div>`;
              list.prepend(div);
            } else if (tab === 'tasks') {
              const pane = document.getElementById('tab-tasks');
              const node = document.createElement('div');
              node.className = 'note-box';
              node.style = 'border:3px solid #d0d7de; display:flex; align-items:center; justify-content:center;';
              node.innerHTML = `<div class="note-title">${data.titulo || payload.titulo}</div>`;
              pane.prepend(node);
            } else if (tab === 'calendar') {
              const pane = document.getElementById('tab-calendar');
              const node = document.createElement('div');
              node.className = 'note-box';
              node.style = 'border:3px solid #dfe7ff; display:flex; align-items:center; justify-content:center;';
              node.innerHTML = `<div class="note-title">${data.titulo || payload.titulo}</div>`;
              pane.prepend(node);
            }
          } else {
            const msg = data.message || 'Error al crear';
            alert(msg);
          }
        } catch (err) {
          console.error('Error creating quick item:', err);
          alert('Error en la conexión al servidor');
        }
      }

      // Inicialización de la pestaña por defecto se hará después de que el DOM se haya renderizado.

      // Attach handlers for task checklist toggles (delegated)
      document.addEventListener('change', async function(e) {
        if (!e.target.matches('.subtask-checkbox-task')) return;
        const cb = e.target;
        const taskId = cb.getAttribute('data-task-id');
        if (!taskId) return;

        // build checklist from DOM for this task
        const container = cb.closest('.note-box');
        const checkboxes = Array.from(container.querySelectorAll('.subtask-checkbox-task'));
        const titles = Array.from(container.querySelectorAll('.subtask-title')).map(s => s.textContent.trim());
        const checklist = checkboxes.map((c, i) => ({ title: titles[i] || '', completed: c.checked }));

        try {
          const res = await fetch('/api/v1/tasks/' + taskId, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
            body: JSON.stringify({ subtareas: checklist })
          });
          // optional: handle response
          if (!res.ok) {
            console.error('Failed to update task checklist', await res.text());
          }
        } catch (err) {
          console.error('Error updating checklist:', err);
        }
      });
    </script>
    <div class="container" style="padding:2rem 2rem 4rem;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem">
        <div style="font-weight:700; font-size:1.25rem">organize ia</div>
        <div style="display:flex; gap:12px;">
          <button class="nav-pill" data-tab="notes" onclick="switchTab('notes')">NOTAS</button>
          <button class="nav-pill" data-tab="tasks" onclick="switchTab('tasks')">TAREAS</button>
          <button class="nav-pill" data-tab="calendar" onclick="switchTab('calendar')">CALENDARIO</button>
        </div>
        <div style="min-width:48px; text-align:right">
          <!-- user icon already present in header; keep empty here -->
        </div>
      </div>

      <style>
        .nav-pill { display:inline-block; padding:10px 22px; border-radius:12px; border:2px solid #111; text-decoration:none; color:#111; font-weight:700; }
        .note-box { padding:22px; border-radius:6px; margin-bottom:18px; background:#fff; }
        .note-title { font-weight:700; text-align:center; font-size:18px; }
        .floating-add { position: fixed; right: 28px; bottom: 28px; width:56px; height:56px; border-radius:50%; background:#fff; border:2px solid #111; display:flex; align-items:center; justify-content:center; font-size:28px; cursor:pointer; }
      </style>

      <div id="tabs-content">
        <div id="tab-notes" class="tab-pane">
          <div id="notes-list">
            @php $colors = ['#f7d36c','#5cc27a','#ffb36b']; @endphp
            @if(isset($notas) && $notas->count())
                @foreach($notas as $i => $n)
                  @php $c = $n->color ?? $colors[$i % count($colors)]; @endphp
                <a href="/organizer/notas/{{ $n->id }}" style="text-decoration:none; color:inherit">
                  <div class="note-box" style="border:4px solid {{ $c }};">
                    <div class="note-title">{{ $n->titulo ?? 'Nota' }}</div>
                  </div>
                </a>
              @endforeach
            @else
              <div style="color:#666; padding:22px; border:2px dashed #ddd; border-radius:6px">No hay notas aún.</div>
            @endif
          </div>
        </div>

        <div id="tab-tasks" class="tab-pane" style="display:none">
          @if(isset($tareas) && $tareas->count())
            @foreach($tareas as $t)
              <div class="note-box" style="border:3px solid #d0d7de; padding:12px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                  <div style="font-weight:700; font-size:16px;">{{ $t->titulo ?? 'Lista' }}</div>
                  <div style="font-size:12px; color:#666">{{ optional($t->fecha_limite)->format('Y-m-d') ?? '' }}</div>
                </div>

                <div>
                  @if(!empty($t->subtareas) && is_array($t->subtareas))
                    <ul style="list-style:none; padding:0; margin:0">
                      @foreach($t->subtareas as $i => $s)
                        <li style="padding:6px 0; display:flex; gap:8px; align-items:center">
                          <input type="checkbox" class="subtask-checkbox-task" data-task-id="{{ $t->id }}" data-idx="{{ $i }}" {{ !empty($s['completed']) ? 'checked' : '' }} />
                          <span class="subtask-title">{{ $s['title'] ?? '' }}</span>
                        </li>
                      @endforeach
                    </ul>
                  @else
                    <ul style="list-style:none; padding:0; margin:0">
                      <li style="padding:6px 0; display:flex; gap:8px; align-items:center">
                        <input type="checkbox" class="subtask-checkbox-task" data-task-id="{{ $t->id }}" data-idx="0" {{ ($t->estado ?? '') == 'completada' ? 'checked' : '' }} />
                        <span class="subtask-title">{{ $t->titulo ?? 'Tarea' }}</span>
                      </li>
                    </ul>
                  @endif
                </div>
              </div>
            @endforeach
          @else
            <div style="color:#666; padding:22px; border:2px dashed #ddd; border-radius:6px">No hay tareas aún.</div>
          @endif
        </div>

        <div id="tab-calendar" class="tab-pane" style="display:none">
          <?php
            // Prepare events for frontend calendar
            $jsEvents = collect([]);
            if (isset($eventos) && $eventos->count()) {
              $jsEvents = $eventos->map(function($e){
                return [
                  'id' => $e->id,
                  'titulo' => $e->titulo,
                  'inicio' => optional($e->inicio)->toDateTimeString(),
                  'fin' => optional($e->fin)->toDateTimeString(),
                  'color' => $e->color ?? null,
                ];
              });
            }
          ?>

          <div style="display:flex; gap:16px; align-items:flex-start;">
            <div style="flex:1;">
              <div id="cal-grid" style="display:grid; 
                   grid-template-columns: repeat(7, 1fr); 
                  gap:6px; margin-top:12px;">
                </div>
                <div style="margin-bottom:12px;">
                  <div style="background:#6aa84f; color:#fff; padding:10px 12px; border-radius:8px; text-align:center; font-weight:800; font-size:18px;"> 
                    <span id="cal-month" style="letter-spacing:1px"></span>
                  </div>
                  <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
                    <div style="display:flex; gap:8px; align-items:center;">
                      <button id="cal-prev" style="padding:6px 10px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer;">◀</button>
                      <button id="cal-next" style="padding:6px 10px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer;">▶</button>
                    </div>
                    <div style="font-size:13px; color:#666">Mes</div>
                  </div>
                </div>
                <div style="margin-top:8px;">
                  <?php
                    // Render calendar as HTML table server-side for guaranteed grid visibility
                    $now = \Carbon\Carbon::now();
                    $first = $now->copy()->startOfMonth();
                    $startOffset = ($first->dayOfWeek + 6) % 7; // Monday=0
                    $daysInMonth = $first->daysInMonth;
                    $weeks = ceil(($startOffset + $daysInMonth) / 7);
                    $weekdays = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
                  ?>

                  <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
                    <thead>
                      <tr>
                        <?php foreach($weekdays as $wd): ?>
                          <th style="background:#6aa84f; color:#fff; padding:10px 6px; font-weight:800; border:2px solid #6aa84f;"><?php echo $wd; ?></th>
                        <?php endforeach; ?>
                      </tr>
                    </thead>
                    <tbody>
                      <?php for($w=0;$w<$weeks;$w++): ?>
                        <tr>
                          <?php for($d=0;$d<7;$d++):
                            $cellIndex = $w*7 + $d - $startOffset + 1;
                            if ($cellIndex < 1 || $cellIndex > $daysInMonth) {
                              echo '<td style="height:92px; border:2px solid #6aa84f; vertical-align:top; background:#fff;"></td>';
                            } else {
                              $cellDate = $first->copy()->day($cellIndex);
                              $isToday = $cellDate->isSameDay($now);
                              $dow = $cellDate->dayOfWeek; // 0 Sunday .. 6 Saturday
                              $style = 'height:92px; border:2px solid #6aa84f; vertical-align:top; padding:8px; cursor:pointer; transition:box-shadow 0.2s;';
                              if ($dow === 6) $style .= ' background:#f4e7c2;';
                              if ($dow === 0) $style .= ' background:#e6e6e6;';
                              if ($isToday) $style .= ' background:#fff1b8; border:3px solid #4a8f3a;';
                              $dateStr = $cellDate->format('Y-m-d');
                              echo '<td class="cal-cell" data-date="' . $dateStr . '" style="' . $style . '" onmouseover="this.style.boxShadow=\'inset 0 0 8px rgba(106,168,79,0.3)\'" onmouseout="this.style.boxShadow=\'\';">';
                              echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><div style="font-weight:700">' . $cellIndex . '</div></div>';

                              // events for the day
                              $evCount = 0;
                              foreach($jsEvents as $ev) {
                                if (empty($ev['inicio'])) continue;
                                $evDate = (new DateTime($ev['inicio']))->format('Y-m-d');
                                if ($evDate === $cellDate->format('Y-m-d')) {
                                  $bg = $ev['color'] ?? '#f0f6ff';
                                  $titulo = htmlspecialchars($ev['titulo'] ?? '');
                                  echo "<a href=\"/organizer/eventos/" . ($ev['id'] ?? '') . "\" style=\"display:block; padding:6px 8px; margin-bottom:6px; border-radius:6px; text-decoration:none; color:#111; background:{$bg};\">{$titulo}</a>";
                                  $evCount++; if ($evCount >= 3) break;
                                }
                              }

                              echo '</td>';
                            }
                          endfor; ?>
                        </tr>
                      <?php endfor; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div style="width:320px;">
              <div style="background:#fff; padding:14px; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.04);">
                <h3 style="margin-top:0; margin-bottom:8px;">Eventos próximos</h3>
                <div id="upcoming-events">
                  @if(isset($eventos) && $eventos->count())
                    @foreach($eventos as $e)
                      <?php
                        $now = \Carbon\Carbon::now();
                        $eventDate = optional($e->inicio);
                        $timeLabel = '';
                        if ($eventDate) {
                          $diff = (int)$eventDate->diffInDays($now); // absolute difference in days
                          $isFuture = $eventDate->isFuture();
                          if ($diff === 0) $timeLabel = '(Hoy)';
                          elseif ($diff === 1 && $isFuture) $timeLabel = '(Mañana)';
                          elseif ($diff > 1 && $isFuture) $timeLabel = "(en $diff días)";
                          elseif ($diff > 0 && !$isFuture) $timeLabel = "(hace $diff días)";
                        }
                      ?>
                      <div style="padding:8px 6px; border-bottom:1px solid #f0f0f0;">
                        <div style="font-weight:600">{{ $e->titulo }}</div>
                        <div style="font-size:13px; color:#666">{{ optional($e->inicio)->format('Y-m-d H:i') }} {{ $timeLabel }}</div>
                      </div>
                    @endforeach
                  @else
                    <div style="color:#666">No hay eventos próximos.</div>
                  @endif
                </div>
              </div>
            </div>
          </div>

          <script>
            const SERVER_EVENTS = @json($jsEvents->values());
            (function(){
              // Calendar state
              let current = new Date();
              // Start week on Monday
              const weekdayNames = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

              function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
              function endOfMonth(d){ return new Date(d.getFullYear(), d.getMonth()+1, 0); }

              function renderCalendar(date){
                const root = document.getElementById('cal-grid');
                const monthLabel = document.getElementById('cal-month');
                root.innerHTML = '';
                const year = date.getFullYear();
                const month = date.getMonth();
                monthLabel.textContent = date.toLocaleString('default', { month: 'long', year: year }).toUpperCase();

                // header row with weekdays (green)
                weekdayNames.forEach(w => {
                  const h = document.createElement('div');
                  h.style.fontWeight = '700'; h.style.padding = '8px 6px'; h.style.textAlign = 'center'; h.style.background = '#6aa84f'; h.style.color = '#fff'; h.style.borderRadius = '6px';
                  h.textContent = w;
                  root.appendChild(h);
                });

                const s = startOfMonth(date);
                // get index of first day in Monday-start grid
                let firstDayIndex = s.getDay(); // 0 Sun .. 6 Sat
                // convert to Monday-start index
                firstDayIndex = (firstDayIndex + 6) % 7; // 0=Mon .. 6=Sun

                const totalDays = endOfMonth(date).getDate();
                // fill blanks before first day
                for(let i=0;i<firstDayIndex;i++){
                  const cell = document.createElement('div');
                  cell.style.minHeight = '80px'; cell.style.padding='6px'; cell.style.borderRadius='6px'; cell.style.background='#fff';
                  root.appendChild(cell);
                }

                // map events by date
                const evMap = {};
                SERVER_EVENTS.forEach(ev => {
                  if (!ev.inicio) return;
                  const d = new Date(ev.inicio);
                  const key = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                  evMap[key] = evMap[key] || [];
                  evMap[key].push(ev);
                });

                for(let day=1; day<=totalDays; day++){
                  const cell = document.createElement('div');
                  cell.style.minHeight = '80px'; cell.style.padding='8px'; cell.style.borderRadius='6px'; cell.style.background='#fff'; cell.style.border='1px solid #6aa84f';
                  // shade weekends: Saturday (6) beige, Sunday (0) light gray
                  const cellDate = new Date(year, month, day);
                  const dow = cellDate.getDay();
                  if (dow === 6) { // Saturday
                    cell.style.background = '#f4e7c2';
                  } else if (dow === 0) { // Sunday
                    cell.style.background = '#e6e6e6';
                  }
                  // highlight today
                  const today = new Date();
                  if (today.getFullYear() === cellDate.getFullYear() && today.getMonth() === cellDate.getMonth() && today.getDate() === cellDate.getDate()) {
                    cell.style.background = '#fff1b8';
                    cell.style.border = '2px solid #4a8f3a';
                  }
                  const dateStr = year + '-' + String(month+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
                  const top = document.createElement('div'); top.style.display='flex'; top.style.justifyContent='space-between'; top.style.alignItems='center'; top.style.marginBottom='6px';
                  const dn = document.createElement('div'); dn.style.fontWeight='700'; dn.textContent = day;
                  top.appendChild(dn);
                  cell.appendChild(top);

                  const evs = evMap[dateStr] || [];
                  evs.slice(0,3).forEach(ev => {
                    const b = document.createElement('a');
                    b.href = '/organizer/eventos/' + (ev.id || '');
                    b.style.display='block'; b.style.padding='6px 8px'; b.style.marginBottom='6px'; b.style.borderRadius='6px'; b.style.textDecoration='none'; b.style.color='#111';
                    b.style.background = ev.color || '#f0f6ff';
                    b.textContent = ev.titulo || '';
                    cell.appendChild(b);
                  });

                  root.appendChild(cell);
                }
              }

              document.getElementById('cal-prev').addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()-1, 1); renderCalendar(current); });
              document.getElementById('cal-next').addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()+1, 1); renderCalendar(current); });

              // initial render
              renderCalendar(current);
            })();
          </script>
        </div>
      </div>

      <a href="#" class="floating-add" title="Agregar" onclick="onAddClick()">+</a>

      <div id="quick-add-modal"></div>
     
      <div id="task-lists-root" style="max-width:900px; margin: 1.5rem auto 3rem; background:#fff; padding:16px; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.04);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <h2 style="margin:0; font-size:1.1rem">Listas (Mercado, Compras...)</h2>
          <button id="new-list-btn" style="padding:8px 12px; background:#0b74de; color:white; border:none; border-radius:6px; cursor:pointer;">+ Nueva lista</button>
        </div>
        <div id="lists-container">Cargando listas...</div>
      </div>
    
    <script>
      
      document.addEventListener('DOMContentLoaded', function(){
        try { if (typeof switchTab === 'function') switchTab('calendar'); } catch(e){ console.error(e); }
        
        // Add event listeners to calendar cells
        document.querySelectorAll('.cal-cell').forEach(cell => {
          cell.addEventListener('click', function(e) {
            // Don't open modal if clicking on event links
            if (e.target.tagName === 'A' || e.target.closest('a')) return;
            
            const dateStr = this.getAttribute('data-date');
            if (dateStr) {
              const [year, month, day] = dateStr.split('-');
              openDayEventsModal(parseInt(year), parseInt(month), parseInt(day));
            }
          });
        });
      });
    </script>
    
      <script>
        (function(){
          const root = document.getElementById('task-lists-root');
          const listsContainer = document.getElementById('lists-container');
          const newBtn = document.getElementById('new-list-btn');
          const csrf = document.querySelector('meta[name="csrf-token"]').content || '';

          newBtn.addEventListener('click', () => openListModal());

          async function loadLists(){
            listsContainer.innerHTML = 'Cargando...';
            try {
              const res = await fetch('/task-lists');
              if (!res.ok) {
                // likely 401 redirect to login or other error
                if (res.status === 401 || res.status === 302) {
                  listsContainer.innerHTML = '<div style="color:#c62828">No autenticado. Inicia sesión para ver tus listas.</div>';
                  return;
                }
                throw new Error('Error en la petición: ' + res.status);
              }

              const contentType = res.headers.get('content-type') || '';
              if (!contentType.includes('application/json')) {
                const text = await res.text();
                console.error('Respuesta no JSON:', text.slice(0,200));
                listsContainer.innerHTML = '<div style="color:#c62828">Respuesta inesperada del servidor.</div>';
                return;
              }

              const lists = await res.json();
              renderLists(lists);
            } catch(err){
              listsContainer.innerHTML = '<div style="color:#c62828">Error cargando listas</div>';
              console.error(err);
            }
          }

          function renderLists(lists){
            if (!lists || lists.length === 0) {
              listsContainer.innerHTML = '<div style="color:#666">Aún no hay listas. Crea una.</div>';
              return;
            }
            listsContainer.innerHTML = '';
            lists.forEach(list => {
              const el = document.createElement('div');
              el.style.borderTop = '1px solid #eef2f6';
              el.style.padding = '8px 0';
              const title = document.createElement('div');
              title.style.display = 'flex'; title.style.justifyContent = 'space-between'; title.style.alignItems = 'center';
              const t = document.createElement('strong'); t.textContent = list.title || 'Sin título';
              title.appendChild(t);
              const actions = document.createElement('div');
              const editBtn = document.createElement('button'); editBtn.textContent = 'Editar'; editBtn.style.marginRight='8px'; editBtn.style.cursor='pointer';
              editBtn.addEventListener('click', () => openListModal(list));
              actions.appendChild(editBtn);
              title.appendChild(actions);
              el.appendChild(title);

              const ul = document.createElement('ul'); ul.style.margin = '8px 0 0 18px';
              (list.items||[]).forEach(it => {
                const li = document.createElement('li'); li.style.display='flex'; li.style.alignItems='center'; li.style.gap='8px';
                const cb = document.createElement('input'); cb.type='checkbox'; cb.checked = !!it.completed;
                cb.addEventListener('change', async () => {
                  try {
                    await fetch(`/task-lists/${list.id}/items/${it.id}`, {
                      method: 'PUT',
                      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                      body: JSON.stringify({ completed: cb.checked })
                    });
                  } catch(e){ console.error(e); }
                });
                const name = document.createElement('span'); name.textContent = it.name;
                name.style.flex = '1';
                name.contentEditable = true;
                name.addEventListener('blur', async () => {
                  try {
                    const newName = name.textContent.trim();
                    if (newName !== it.name) {
                      await fetch(`/task-lists/${list.id}/items/${it.id}`, {
                        method: 'PUT', headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf },
                        body: JSON.stringify({ name: newName })
                      });
                    }
                  } catch(e){ console.error(e); }
                });
                li.appendChild(cb); li.appendChild(name);
                ul.appendChild(li);
              });
              el.appendChild(ul);
              listsContainer.appendChild(el);
            });
          }

          function openListModal(list){
            const container = document.getElementById('quick-add-modal');
            const isEdit = !!(list && list.id);
            const modalHtml = document.createElement('div');
            modalHtml.style.position='fixed'; modalHtml.style.left=0; modalHtml.style.top=0; modalHtml.style.right=0; modalHtml.style.bottom=0; modalHtml.style.background='rgba(0,0,0,0.4)'; modalHtml.style.display='flex'; modalHtml.style.alignItems='center'; modalHtml.style.justifyContent='center'; modalHtml.style.zIndex=3000;
            const box = document.createElement('div'); box.style.background='white'; box.style.padding='20px'; box.style.borderRadius='8px'; box.style.width='480px'; box.style.maxWidth='94%';

            box.innerHTML = `
              <h3 style="margin-top:0">${isEdit ? 'Editar lista' : 'Nueva lista'}</h3>
              <div style="margin-bottom:8px;"><label style="font-weight:600; display:block; margin-bottom:6px">Título</label><input id="list-title-input" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px" value="${isEdit ? escapeHtml(list.title) : ''}"></div>
              <div id="items-area" style="max-height:300px; overflow:auto; margin-bottom:8px"></div>
              <div style="display:flex; gap:8px; justify-content:flex-end;"><button id="add-item-btn" style="padding:8px 10px">+ Item</button><button id="save-list-btn" style="background:#0b74de; color:white; padding:8px 12px; border:none; border-radius:6px">Guardar</button><button id="close-list-btn" style="padding:8px 10px">Cancelar</button></div>
            `;

            modalHtml.appendChild(box);
            container.innerHTML = '';
            container.appendChild(modalHtml);

            const itemsArea = box.querySelector('#items-area');
            const addItemBtn = box.querySelector('#add-item-btn');
            const saveBtn = box.querySelector('#save-list-btn');
            const closeBtn = box.querySelector('#close-list-btn');

            function addItemRow(item){
              const row = document.createElement('div'); row.style.display='flex'; row.style.gap='8px'; row.style.marginBottom='8px'; row.style.alignItems='center'
              const cb = document.createElement('input'); cb.type='checkbox'; cb.checked = !!(item && item.completed);
              const input = document.createElement('input'); input.type='text'; input.value = item?.name || '';
              input.style.flex='1'; input.style.padding='8px'; input.style.border='1px solid #ddd'; input.style.borderRadius='6px';
              const rem = document.createElement('button'); rem.textContent='Eliminar'; rem.style.padding='6px 8px'; rem.style.cursor='pointer';
              rem.addEventListener('click', () => row.remove());
              if (item && item.id) row.dataset.itemId = item.id;
              row.appendChild(cb); row.appendChild(input); row.appendChild(rem);
              itemsArea.appendChild(row);
            }

            // populate existing items
            if (isEdit && Array.isArray(list.items)) {
              list.items.forEach(it => addItemRow(it));
            } else {
              addItemRow();
            }

            addItemBtn.addEventListener('click', (e)=>{ e.preventDefault(); addItemRow(); });

            closeBtn.addEventListener('click', () => { container.innerHTML = ''; });

            saveBtn.addEventListener('click', async () => {
              const title = box.querySelector('#list-title-input').value.trim();
              if (!title) { alert('El título es obligatorio'); return; }
              const rows = Array.from(itemsArea.children);
              const items = rows.map(r => ({ id: r.dataset.itemId, name: r.querySelector('input[type=text]').value.trim(), completed: r.querySelector('input[type=checkbox]').checked } )).filter(x => x.name.length>0);

              try {
                if (!isEdit) {
                  const res = await fetch('/task-lists', { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title, items }) });
                  if (!res.ok) throw new Error('Error creando');
                } else {
                  // update title
                  await fetch(`/task-lists/${list.id}`, { method: 'PUT', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title }) });
                  // update or create items
                  for (const it of items) {
                    if (it.id) {
                      await fetch(`/task-lists/${list.id}/items/${it.id}`, { method: 'PUT', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ name: it.name, completed: it.completed }) });
                    } else {
                      await fetch(`/task-lists/${list.id}/items`, { method: 'POST', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ name: it.name }) });
                    }
                  }
                }
                container.innerHTML = '';
                await loadLists();
              } catch(e){ console.error(e); alert('Error al guardar'); }
            });
          }

          function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

          // initial load
          loadLists();
        })();
      </script>
      </div>
    </body>
  </html>
