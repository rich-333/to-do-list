<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Editar Tarea</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111 }
      .container { max-width:900px; margin:2.5rem auto; background:#fff; padding:1.25rem; border-radius:8px }
      input, textarea, select { width:100%; padding:0.5rem; border:1px solid #d6dde6; border-radius:6px }
      button { background:#0b74de; color:#fff; border:none; padding:0.5rem 0.8rem; border-radius:6px }
      pre { background:#f3f6f9; padding:0.75rem; border-radius:6px }
      .subtask { display:block; margin-top:0.4rem }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Editar Tarea</h1>
      <form id="edit-task">
        <label>Conjunto</label>
        <input id="conjunto" name="conjunto" value="{{ $task->conjunto }}" />

        <label>Título</label>
        <input id="titulo" name="titulo" value="{{ $task->titulo }}" />

        <label>Descripción</label>
        <textarea id="descripcion" name="descripcion">{{ $task->descripcion }}</textarea>

        <label>Prioridad</label>
        <select id="prioridad" name="prioridad">
          <option {{ $task->prioridad=='baja' ? 'selected' : '' }}>baja</option>
          <option {{ $task->prioridad=='media' ? 'selected' : '' }}>media</option>
          <option {{ $task->prioridad=='alta' ? 'selected' : '' }}>alta</option>
        </select>

        <label>Fecha límite</label>
        <input id="fecha_limite" name="fecha_limite" type="datetime-local" value="{{ optional($task->fecha_limite)->format('Y-m-d\TH:i') }}" />

        <label>Etiquetas (comma)</label>
        <input id="etiquetas" name="etiquetas" value="{{ implode(',', $task->etiquetas ?? []) }}" />

        <label>Checklist</label>
        <div id="checklist">
          @if(!empty($task->subtareas))
            <ul style="list-style:none; padding:0; margin:0">
              @foreach($task->subtareas as $i => $s)
                <li style="padding:0.3rem 0; display:flex; gap:0.6rem; align-items:center">
                  <input class="subtask-checkbox" type="checkbox" data-idx="{{ $i }}" {{ !empty($s['completed']) ? 'checked' : '' }} />
                  <span class="subtask-title">{{ $s['title'] ?? '' }}</span>
                </li>
              @endforeach
            </ul>
          @else
            <div style="color:#666">No hay subtareas.</div>
          @endif
        </div>

        <label>Añadir subtarea (texto)</label>
        <div id="subtasks-list">
          @foreach($task->subtareas ?? [] as $i => $s)
            <input class="subtask" value="{{ $s['title'] ?? '' }}" />
          @endforeach
          @if(empty($task->subtareas))
            <input class="subtask" placeholder="Subtarea 1" />
          @endif
        </div>
        <button id="add-subtask" type="button">Añadir subtarea</button>

        <p style="margin-top:0.75rem"><button type="submit">Guardar</button> <a href="/">Volver</a> <button id="delete-task" type="button" style="background:#e53935; color:white; border:none; padding:0.35rem 0.6rem; border-radius:6px; margin-left:8px">🗑️ Eliminar</button></p>
      </form>

      <pre id="output"></pre>
    </div>

    <script>
    (function(){
      const form = document.getElementById('edit-task');
      const out = document.getElementById('output');
      form.addEventListener('submit', async (e) => {
        e.preventDefault(); out.textContent='Guardando...';
        try{
          const fd = new FormData(form);
          const etiquetas = (fd.get('etiquetas')||'').split(',').map(s=>s.trim()).filter(Boolean);
          const subtareaInputs = Array.from(document.querySelectorAll('.subtask'));
          const subtareas = subtareaInputs.map(i=>({title:i.value.trim(), completed:false})).filter(s=>s.title.length);
          function toIso(v){ if(!v) return null; const d=new Date(v); return isNaN(d)? v.replace('T',' '): d.toISOString(); }
          const payload = {
            conjunto: fd.get('conjunto')||null,
            titulo: fd.get('titulo'),
            descripcion: fd.get('descripcion'),
            prioridad: fd.get('prioridad'),
            fecha_limite: toIso(fd.get('fecha_limite')),
            etiquetas: etiquetas,
            subtareas: subtareas
          };
          const res = await fetch('/api/v1/tasks/{{ $task->id }}', { method: 'PUT', headers:{'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify(payload)});
          const text = await res.text(); let data; try{data=JSON.parse(text)}catch(e){data=text}
          out.textContent = 'HTTP '+res.status+'\n'+JSON.stringify(data,null,2);
        }catch(err){ out.textContent='Error: '+(err.message||err); console.error(err) }
      });

      // Delete task
      document.getElementById('delete-task')?.addEventListener('click', async (e) => {
        e.preventDefault();
        if (!confirm('¿Eliminar esta tarea?')) return;
        try {
          const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
          const res = await fetch('/api/v1/tasks/{{ $task->id }}', { method: 'DELETE', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
          if (res.ok) window.location.href = '/';
          else {
            const txt = await res.text(); alert('Error eliminando tarea: ' + (txt || res.statusText));
          }
        } catch (err) { console.error(err); alert('Error eliminando tarea'); }
      });

      document.getElementById('add-subtask').addEventListener('click', function(){
        const list=document.getElementById('subtasks-list'); const idx=list.querySelectorAll('.subtask').length+1; const input=document.createElement('input'); input.className='subtask'; input.placeholder='Subtarea '+idx; list.appendChild(document.createElement('br')); list.appendChild(input);
      });

      // Handle checklist checkbox toggles and persist immediately
      document.querySelectorAll('.subtask-checkbox').forEach(ch => {
        ch.addEventListener('change', async function(){
          const idx = parseInt(this.dataset.idx,10);
          // build updated subtareas array from DOM
          const titles = Array.from(document.querySelectorAll('#subtasks-list .subtask')).map(i=>i.value.trim()).filter(Boolean);
          const checklist = Array.from(document.querySelectorAll('.subtask-checkbox')).map((cb, i)=>({ title: (cb.nextElementSibling?.textContent||titles[i]||'').trim(), completed: cb.checked }));
          try{
            const res = await fetch('/api/v1/tasks/{{ $task->id }}', { method: 'PATCH', headers:{'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify({ subtareas: checklist }) });
            // optional: show quick feedback
            const outp = document.getElementById('output'); const txt = await res.text(); let data; try{data=JSON.parse(txt)}catch(e){data=txt}
            outp.textContent = 'HTTP '+res.status + '\n' + JSON.stringify(data,null,2);
          }catch(err){ console.error(err); }
        });
      });
    })();
    </script>
  </body>
</html>
