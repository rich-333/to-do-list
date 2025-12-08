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
      .nav-pill { display:inline-block; padding:10px 22px; border-radius:12px; border:2px solid #111; text-decoration:none; color:#111; font-weight:700; }
      .note-box { padding:22px; border-radius:6px; margin-bottom:18px; background:#fff; }
      .note-title { font-weight:700; text-align:center; font-size:18px; }
      .floating-add { position: fixed; right: 28px; bottom: 28px; width:56px; height:56px; border-radius:50%; background:#fff; border:2px solid #111; display:flex; align-items:center; justify-content:center; font-size:28px; cursor:pointer; }
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
    
    <script src="{{ asset('js/tabs.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
    <script src="{{ asset('js/modals.js') }}"></script>
    <script src="{{ asset('js/calendar.js') }}"></script>
    <script src="{{ asset('js/quick-add.js') }}"></script>
    <script src="{{ asset('js/task-lists.js') }}"></script>
    
    <div class="container" style="padding:2rem 2rem 4rem;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; gap:1rem; flex-wrap:wrap">
        <div style="font-weight:700; font-size:1.25rem">organize ia</div>
        
        <div style="display:flex; gap:12px;">
          <button class="nav-pill" data-tab="notes" onclick="switchTab('notes')">NOTAS</button>
          <button class="nav-pill" data-tab="tasks" onclick="switchTab('tasks')">LISTAS</button>
          <button class="nav-pill" data-tab="calendar" onclick="switchTab('calendar')">CALENDARIO</button>
        </div>
        <div style="min-width:48px; text-align:right">
          <!-- user icon already present in header; keep empty here -->
        </div>
      </div>

      <div id="tabs-content">
        <!-- INCLUIR COMPONENTES DE PESTAÑAS -->
        @include('organizer.tabs.notes')
        @include('organizer.tabs.tasks')
        @include('organizer.tabs.calendar')
      </div>

      <a href="#" class="floating-add" title="Agregar" onclick="onAddClick()">+</a>

      <!-- Add modals containers -->
      <div id="quick-add-modal"></div>
    </div>

    <script>
      // Agregar event listeners a las celdas del calendario
      document.addEventListener('DOMContentLoaded', function(){
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
  </body>
</html>
