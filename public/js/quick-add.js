// === QUICK ADD MODAL ===

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
          ${QA_PASTEL_COLORS.map(c => `<button type="button" class="qa-color-swatch" data-color="${c}" style="width:36px;height:36px;border-radius:6px;border:2px solid transparent;background:${c};cursor:pointer;"></button>`).join('')}
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
