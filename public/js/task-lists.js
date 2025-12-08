// === LISTAS DE TAREAS ===

(function(){
  let listsContainer = null;
  let newBtn = null;
  const csrf = document.querySelector('meta[name="csrf-token"]').content || '';

  // Esperar a que el botón exista
  function initializeListsModule() {
    // Intenta obtener los elementos en cada invocación
    listsContainer = document.getElementById('lists-container');
    newBtn = document.getElementById('new-list-btn');
    
    if (!newBtn || !listsContainer) {
      console.log('[task-lists.js] Esperando DOM elements... newBtn:', !!newBtn, 'listsContainer:', !!listsContainer);
      setTimeout(initializeListsModule, 100);
      return;
    }
    
    console.log('[task-lists.js] ✓ Módulo inicializado correctamente');
    newBtn.addEventListener('click', () => openListModal());
    loadLists();
  }

  async function loadLists(){
    listsContainer.innerHTML = 'Cargando...';
    try {
      console.log('[task-lists.js] Iniciando loadLists()');
      const res = await fetch('/task-lists', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } });
      console.log('[task-lists.js] Respuesta recibida:', res.status, res.statusText);

      if (!res.ok) {
        // Handle auth redirect first
        if (res.status === 401 || res.status === 302) {
          console.warn('[task-lists.js] Usuario no autenticado (status:', res.status + ')');
          listsContainer.innerHTML = '<div style="color:#c62828">No autenticado. Inicia sesión para ver tus listas.</div>';
          return;
        }

        // Try to read response body (JSON or text) to show the real error
        const contentType = res.headers.get('content-type') || '';
        let serverBody = '';
        try {
          if (contentType.includes('application/json')) {
            const j = await res.json();
            serverBody = JSON.stringify(j, null, 2);
          } else {
            serverBody = await res.text();
          }
        } catch (e) {
          serverBody = '[no se pudo leer el cuerpo de la respuesta]';
        }

        console.error('[task-lists.js] Error en respuesta HTTP', res.status, res.statusText, serverBody);
        const excerpt = String(serverBody).slice(0, 2000);
        listsContainer.innerHTML = '<div style="color:#c62828">Respuesta inesperada del servidor. <details style="white-space:pre-wrap; max-height:300px; overflow:auto;"><summary>Mostrar error</summary><pre>' + escapeHtml(excerpt) + '</pre></details></div>';
        return;
      }

      const contentType = res.headers.get('content-type') || '';
      if (!contentType.includes('application/json')) {
        const text = await res.text();
        console.error('[task-lists.js] Respuesta no JSON (completa):', text);
        const excerpt = text.slice(0, 1000);
        // Mostrar mensaje amigable y permitir al usuario expandir el error exacto (escapado)
        listsContainer.innerHTML = '<div style="color:#c62828">Respuesta inesperada del servidor. <details style="white-space:pre-wrap; max-height:200px; overflow:auto;"><summary>Mostrar error</summary><pre>' + escapeHtml(excerpt) + '</pre></details></div>';
        return;
      }

      const lists = await res.json();
      console.log('[task-lists.js] Listas cargadas:', lists);
      renderLists(lists);
    } catch(err){
      listsContainer.innerHTML = '<div style="color:#c62828">Error cargando listas</div>';
      console.error('[task-lists.js] Error:', err);
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
              credentials: 'same-origin',
              headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
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
                method: 'PUT',
                credentials: 'same-origin',
                headers:{ 'Content-Type':'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
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
      <div style="margin-bottom:8px;"><label style="font-weight:600; display:block; margin-bottom:6px">Contexto (opcional, ej: "Cena mexicana")</label><input id="list-context-input" placeholder="Usa esto para sugerencias más específicas" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px"></div>
      <div style="margin-bottom:8px;"><label style="font-weight:600; display:block; margin-bottom:6px">Proveedor IA</label><select id="ai-provider" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:6px"><option value="groq">Groq (recomendado)</option><option value="deepseek">Deepseek</option><option value="gemini">Gemini</option></select>
        <div style="margin-top:6px"><span id="ai-provider-badge" style="display:inline-block;padding:4px 8px;border-radius:12px;background:#eef;color:#124;font-size:12px;font-weight:600">IA: -</span></div>
      </div>
      <div id="items-area" style="max-height:300px; overflow:auto; margin-bottom:8px"></div>
      <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;"><button id="add-item-btn" style="padding:8px 10px">+ Item</button><button id="suggest-items-btn" style="padding:8px 10px; background:#ff9800; color:white; border:none; border-radius:6px; cursor:pointer;">🤖 Sugerir con IA</button><button id="save-list-btn" style="background:#0b74de; color:white; padding:8px 12px; border:none; border-radius:6px">Guardar</button><button id="close-list-btn" style="padding:8px 10px">Cancelar</button></div>
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

    const suggestBtn = box.querySelector('#suggest-items-btn');
    suggestBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      const title = box.querySelector('#list-title-input').value.trim();
      const context = box.querySelector('#list-context-input').value.trim();
      const provider = box.querySelector('#ai-provider').value;

      if (!title) {
        alert('Ingresa un título para obtener sugerencias');
        return;
      }

      suggestBtn.disabled = true;
      suggestBtn.textContent = '⏳ Generando...';

      try {
        const res = await fetch('/ai/suggest-items', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
          body: JSON.stringify({ title, context, provider })
        });

        console.log('[task-lists.js] Respuesta HTTP: status=' + res.status + ', ok=' + res.ok);

        if (!res.ok) {
          let errData;
          try {
            errData = await res.json();
          } catch {
            errData = { error: await res.text() };
          }
          console.error('[task-lists.js] Error en respuesta:', errData);
          const errorMsg = errData.error || 'Error al generar sugerencias (HTTP ' + res.status + ')';
          throw new Error(errorMsg);
        }

        const { items, provider: usedProvider } = await res.json();
        console.log('[task-lists.js] Sugerencias recibidas de ' + usedProvider + ':', items);

        // Limpiar items actuales y agregar sugerencias
        itemsArea.innerHTML = '';
        items.forEach(name => addItemRow({ name }));
        suggestBtn.textContent = `✓ Sugerencias cargadas (${usedProvider})`;
        const badge = box.querySelector('#ai-provider-badge');
        if (badge) { badge.textContent = 'IA: ' + usedProvider; badge.style.display = 'inline-block'; }
      } catch (e) {
        console.error('[task-lists.js] Error en suggestItems:', e);
        alert('Error: ' + e.message);
        suggestBtn.textContent = '🤖 Sugerir con IA';
      } finally {
        suggestBtn.disabled = false;
      }
    });

    closeBtn.addEventListener('click', () => { container.innerHTML = ''; });

    saveBtn.addEventListener('click', async () => {
      const title = box.querySelector('#list-title-input').value.trim();
      if (!title) { alert('El título es obligatorio'); return; }
      const rows = Array.from(itemsArea.children);
      const items = rows.map(r => ({ id: r.dataset.itemId, name: r.querySelector('input[type=text]').value.trim(), completed: r.querySelector('input[type=checkbox]').checked } )).filter(x => x.name.length>0);

      try {
        if (!isEdit) {
          const res = await fetch('/task-lists', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title, items }) });
          if (!res.ok) throw new Error('Error creando');
        } else {
          // update title
          await fetch(`/task-lists/${list.id}`, { method: 'PUT', credentials: 'same-origin', headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title }) });
          // update or create items
          for (const it of items) {
            if (it.id) {
              await fetch(`/task-lists/${list.id}/items/${it.id}`, { method: 'PUT', credentials: 'same-origin', headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ name: it.name, completed: it.completed }) });
            } else {
              await fetch(`/task-lists/${list.id}/items`, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ name: it.name }) });
            }
          }
        }
        container.innerHTML = '';
        await loadLists();
      } catch(e){ console.error(e); alert('Error al guardar'); }
    });
  }

  function escapeHtml(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  // Inicializar cuando el DOM esté listo
  console.log('[task-lists.js] Script cargado. Document readyState:', document.readyState);
  if (document.readyState === 'loading') {
    console.log('[task-lists.js] Esperando DOMContentLoaded...');
    document.addEventListener('DOMContentLoaded', initializeListsModule);
  } else {
    console.log('[task-lists.js] DOM ya está listo, inicializando...');
    initializeListsModule();
  }
})();
