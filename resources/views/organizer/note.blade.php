<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Editar Nota</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111 }
      .container { max-width:900px; margin:2.5rem auto; background:#fff; padding:1.25rem; border-radius:8px }
      input, textarea { width:100%; padding:0.5rem; border:1px solid #d6dde6; border-radius:6px }
      button { background:#0b74de; color:#fff; border:none; padding:0.5rem 0.8rem; border-radius:6px }
      pre { background:#f3f6f9; padding:0.75rem; border-radius:6px }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Editar Nota</h1>
      <form id="edit-note">
        <label>Título</label>
        <input name="titulo" id="titulo" value="{{ $note->titulo }}" />

        <label>Contenido</label>
        <textarea name="contenido" id="contenido">{{ $note->contenido }}</textarea>

        <label>Etiquetas (comma)</label>
        <input name="etiquetas" id="etiquetas" value="{{ implode(',', $note->etiquetas ?? []) }}" />

        <label>Color</label>
        <input type="hidden" name="color" id="color" value="{{ $note->color }}" />
        <div id="color-palette" style="display:flex; gap:8px; margin-top:8px; flex-wrap:wrap;"></div>

        <p style="margin-top:0.75rem"><button type="submit">Guardar</button> <a href="/">Volver</a></p>

        <!-- IA: acciones dinámicas y botones -->
        <div id="ai-actions" style="margin-top:1rem; padding:0.75rem; background:#f3f6f9; border-radius:8px; display:none;">
          <strong>🤖 Acciones sugeridas</strong>
          <div id="actions-list" style="margin-top:0.5rem; display:flex; gap:8px; flex-wrap:wrap;"></div>
          <div id="ai-status" style="margin-top:0.5rem; color:#666; font-size:0.95em"></div>
        </div>

        <p style="margin-top:0.25rem">
          <button id="analyze-btn" type="button" style="background:#9c27b0; color:white; border:none; padding:0.45rem 0.75rem; border-radius:6px;">🔍 Analizar con IA</button>
          <button id="suggest-ia" type="button" style="background:#ff9800; border:none; padding:0.45rem 0.75rem; border-radius:6px; margin-left:8px;">🤖 Sugerir con IA</button>
          <span id="ia-note-msg" style="margin-left:12px;color:#666"></span>
        </p>
      </form>

      <pre id="output"></pre>
    </div>

      <script>
    (function(){
      const csrfMeta = document.createElement('meta');
      csrfMeta.name = 'csrf-token';
      csrfMeta.content = '{{ csrf_token() }}';
      document.head.appendChild(csrfMeta);
      const form = document.getElementById('edit-note');
      const out = document.getElementById('output');
      const suggestBtn = document.getElementById('suggest-ia');
      const iaMsg = document.getElementById('ia-note-msg');
      const NOTE_PASTEL_COLORS = ['#f8c8dc','#ffd8a8','#fff1a8','#c8f0d6','#c8e7ff','#e1c8ff','#f0e1c8','#d8f0ff'];
      const paletteContainer = document.getElementById('color-palette');
      const hiddenColor = document.getElementById('color');
      let selected = hiddenColor.value || NOTE_PASTEL_COLORS[0];

      // render palette
      NOTE_PASTEL_COLORS.forEach(c => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'color-swatch';
        btn.setAttribute('data-color', c);
        btn.style.width = '36px';
        btn.style.height = '36px';
        btn.style.borderRadius = '6px';
        btn.style.border = '2px solid transparent';
        btn.style.background = c;
        btn.style.cursor = 'pointer';
        if (c === selected) btn.style.borderColor = '#111';
        btn.addEventListener('click', () => {
          selected = c;
          hiddenColor.value = c;
          paletteContainer.querySelectorAll('.color-swatch').forEach(x => x.style.borderColor = 'transparent');
          btn.style.borderColor = '#111';
        });
        paletteContainer.appendChild(btn);
      });
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        out.textContent = 'Guardando...';
        try {
          const payload = {
            titulo: document.getElementById('titulo').value,
            contenido: document.getElementById('contenido').value,
            etiquetas: (document.getElementById('etiquetas').value||'').split(',').map(s=>s.trim()).filter(Boolean),
            color: document.getElementById('color').value || null
          };

          const res = await fetch('/api/v1/notes/{{ $note->id }}', {
            method: 'PUT',
            headers: {'Content-Type':'application/json', 'Accept':'application/json'},
            body: JSON.stringify(payload)
          });
          const text = await res.text();
          let data; try{ data = JSON.parse(text); } catch(e){ data = text }
          out.textContent = 'HTTP ' + res.status + '\n' + JSON.stringify(data, null, 2);
        } catch(err){ out.textContent = 'Error: '+(err.message||err); console.error(err) }
      });

      const analyzeBtn = document.getElementById('analyze-btn');
      const aiStatus = document.getElementById('ai-status');
      const actionsList = document.getElementById('actions-list');

      suggestBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        iaMsg.textContent = 'Generando sugerencias...';
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]').content || '';
          const res = await fetch('/api/v1/notes/{{ $note->id }}/ai/suggest', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ provider: 'groq' })
          });

          const data = await res.json();
          if (res.ok && data.items) {
            // Insertar sugerencias en el contenido (append)
            const ta = document.getElementById('contenido');
            ta.value = ta.value + '\n\n' + data.items.map((it, i) => (i+1)+'. '+it).join('\n');
            iaMsg.textContent = data.provider === 'local' ? 'IA no disponible — mostrando sugerencias locales' : 'Sugerencias añadidas ('+data.provider+')';
          } else {
            iaMsg.textContent = 'Error: ' + (data.error || JSON.stringify(data));
          }
        } catch (err) {
          iaMsg.textContent = 'Error al generar sugerencias: '+(err.message||err);
          console.error(err);
        }
        setTimeout(()=>iaMsg.textContent='', 8000);
      });

      analyzeBtn?.addEventListener('click', async (e) => {
        e.preventDefault();
        aiStatus.textContent = '🔄 Analizando...';
        actionsList.innerHTML = '';
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]').content || '';
          const res = await fetch('/api/v1/notes/{{ $note->id }}/ai/analyze', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
          });

          const contentType = (res.headers.get('content-type') || '').toLowerCase();
          let data = null;
          let text = null;
          if (contentType.includes('application/json')) {
            try { data = await res.json(); } catch (e) { text = await res.text(); }
          } else {
            // Response is not JSON (could be HTML login redirect or error page)
            text = await res.text();
            try { data = JSON.parse(text); } catch (e) { /* keep text */ }
          }

          if (res.ok) {
            // Ensure we have a data object
            data = data || {};
            aiStatus.textContent = `📊 Detectado: ${data.type || 'n/a'} (Prioridad: ${data.priority || 'n/a'}) — ${data.word_count || 0} palabras`;
            if (data.suggested_actions && data.suggested_actions.length) {
              document.getElementById('ai-actions').style.display = 'block';
              data.suggested_actions.forEach(action => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = action.label;
                btn.title = action.description || '';
                btn.style.cssText = 'padding:0.5rem 0.85rem; border-radius:6px; background:#673ab7; color:white; border:none; cursor:pointer;';
                btn.addEventListener('click', () => executeAction(action.action));
                actionsList.appendChild(btn);
              });
            } else {
              aiStatus.textContent += ' — Sin acciones sugeridas';
            }
          } else {
            // Mostrar detalle útil para depuración (HTTP status + posible body)
            const bodyMsg = data ? (data.error || JSON.stringify(data)) : (text ? text.substring(0, 800) : 'Sin cuerpo');
            aiStatus.textContent = `❌ HTTP ${res.status} ${res.statusText} — ${bodyMsg}`;
            console.warn('AI analyze error response:', res.status, res.statusText, { data, text });
          }
        } catch (err) {
          aiStatus.textContent = '❌ Error de red/JS: ' + (err.message || String(err));
          console.error('Analyze handler error:', err);
        }
      });

      async function executeAction(action) {
        aiStatus.textContent = '⏳ Ejecutando ' + action + '...';
        const csrf = document.querySelector('meta[name="csrf-token"]').content || '';
        try {
          if (action === 'save_to_calendar') {
            aiStatus.textContent = '📅 Guardando en calendario...';
            try {
              const res = await fetch('/api/v1/notes/{{ $note->id }}/to-event', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf } });
              const text = await res.text();
              let data;
              try {
                data = JSON.parse(text);
              } catch (e) {
                // Si no es JSON válido, mostrar el texto como es (error HTML o plain text)
                aiStatus.textContent = '❌ Error en respuesta: ' + text.substring(0, 300);
                console.error('Response text:', text);
                return;
              }
              if (res.ok) {
                aiStatus.textContent = '✅ Evento creado';
                const eventId = data.id || (data.data && data.data.id) || null;
                const toast = document.createElement('div');
                toast.style.position = 'fixed';
                toast.style.right = '16px';
                toast.style.top = '16px';
                toast.style.background = '#2b6cb0';
                toast.style.color = '#fff';
                toast.style.padding = '12px 16px';
                toast.style.borderRadius = '8px';
                toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
                toast.style.zIndex = 9999;
                toast.innerHTML = '<strong>Evento creado</strong>' + (eventId ? ' — <a href="/organizer/eventos/' + eventId + '" style="color:#ffd; text-decoration:underline; margin-left:8px;">Ir al evento</a>' : '');
                document.body.appendChild(toast);
                setTimeout(() => { toast.style.transition = 'opacity 0.4s'; toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 6000);
              } else {
                aiStatus.textContent = '❌ Error: ' + (data.error || data.message || JSON.stringify(data));
              }
            } catch (err) {
              aiStatus.textContent = '❌ ' + (err.message || err);
              console.error(err);
            }
            return;
          }

          if (action === 'convert_to_task') {
            // Llamar endpoint existente toTask
            const res = await fetch('/api/v1/notes/{{ $note->id }}/to-task', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf } });
            const data = await res.json();
            if (res.ok) {
              aiStatus.textContent = '✅ Tarea creada';
              // Mostrar un toast con enlace a la nueva tarea en lugar de redirigir automáticamente
              const taskId = data.id || (data.data && data.data.id) || null;
              const toast = document.createElement('div');
              toast.style.position = 'fixed';
              toast.style.right = '16px';
              toast.style.top = '16px';
              toast.style.background = '#323232';
              toast.style.color = '#fff';
              toast.style.padding = '12px 16px';
              toast.style.borderRadius = '8px';
              toast.style.boxShadow = '0 6px 18px rgba(0,0,0,0.12)';
              toast.style.zIndex = 9999;
              toast.innerHTML = '<strong>Tarea creada</strong>' + (taskId ? ' — <a href="/organizer/tareas/' + taskId + '" style="color:#ffd; text-decoration:underline; margin-left:8px;">Ir a tarea</a>' : '');
              document.body.appendChild(toast);
              // Eliminar toast tras 6s
              setTimeout(() => { toast.style.transition = 'opacity 0.4s'; toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 6000);
            } else {
              aiStatus.textContent = '❌ Error al crear tarea: ' + (data.message || JSON.stringify(data));
            }
            return;
          }

          if (action === 'summarize') {
            const res = await fetch('/api/v1/notes/{{ $note->id }}/ai/summarize-content', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf } });
            const data = await res.json();
            if (res.ok && data.summary) {
              const ta = document.getElementById('contenido');
              ta.value = data.summary + '\n\n' + ta.value;
              aiStatus.textContent = '✅ Resumen añadido';
            } else {
              aiStatus.textContent = '❌ Error al resumir';
            }
            return;
          }

          if (action === 'expand') {
            const res = await fetch('/api/v1/notes/{{ $note->id }}/ai/expand', { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type':'application/json','X-CSRF-TOKEN': csrf } });
            const data = await res.json();
            if (res.ok && data.expanded) {
              const ta = document.getElementById('contenido');
              ta.value = data.expanded;
              aiStatus.textContent = '✅ Contenido ampliado';
            } else {
              aiStatus.textContent = '❌ Error al ampliar';
            }
            return;
          }

          aiStatus.textContent = 'Acción no soportada: ' + action;
        } catch (err) {
          aiStatus.textContent = '❌ ' + (err.message || err);
        }
      }
    })();
    </script>
  </body>
</html>
