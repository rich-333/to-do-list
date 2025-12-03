<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>OrganizerAI - Events</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111 }
      .container { max-width:900px; margin:2.5rem auto; background:#fff; padding:1.25rem 1.5rem; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.06); }
      input, textarea { width:100%; max-width:520px; padding:0.45rem; border:1px solid #d6dde6; border-radius:6px; margin-bottom:0.5rem }
      label { display:block; margin-top:0.75rem; font-weight:600 }
      button { background:#0b74de; color:#fff; border:none; padding:0.5rem 0.8rem; border-radius:6px; cursor:pointer }
      pre { background:#f3f6f9; padding:0.75rem; border-radius:6px; overflow:auto }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>Eventos (pruebas)</h1>
      <form id="create-event">
        <label for="titulo">Título</label>
        <input id="titulo" name="titulo" placeholder="Título" />

        <label for="descripcion">Descripción</label>
        <textarea id="descripcion" name="descripcion" placeholder="Descripción"></textarea>

        <label for="inicio">Inicio</label>
        <input id="inicio" name="inicio" type="datetime-local" />

        <label for="fin">Fin</label>
        <input id="fin" name="fin" type="datetime-local" />

        <label for="ubicacion">Lugar</label>
        <input id="ubicacion" name="ubicacion" placeholder="Lugar" />

        <label for="fecha_recordatorio">Fecha recordatorio</label>
        <input id="fecha_recordatorio" name="fecha_recordatorio" type="datetime-local" />

        <button type="submit">Create</button>
      </form>

      <pre id="output"></pre>

      <script>
      (function(){
        const out = document.getElementById('output');
        const form = document.getElementById('create-event');
        form.addEventListener('submit', async (e) => {
          e.preventDefault();
          out.textContent = 'Enviando...';
          try {
            const fd = new FormData(form);
            function toIso(v){ if(!v) return null; const d=new Date(v); return isNaN(d)? v.replace('T',' '): d.toISOString(); }
            const payload = {
              titulo: fd.get('titulo'),
              descripcion: fd.get('descripcion'),
              inicio: toIso(fd.get('inicio')),
              fin: toIso(fd.get('fin')),
              ubicacion: fd.get('ubicacion'),
              fecha_recordatorio: toIso(fd.get('fecha_recordatorio')),
              usuario_id: 1
            };

            const res = await fetch('/api/v1/events', { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify(payload)});
            const text = await res.text();
            let data; try{ data = JSON.parse(text); } catch(e){ data = text }
            out.textContent = 'HTTP ' + res.status + '\n' + JSON.stringify(data, null, 2);
          } catch(err){ out.textContent = 'Error: '+(err.message||err); console.error(err) }
        });
      })();
      </script>
    </div>
  </body>
</html>
