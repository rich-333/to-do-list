<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Editar Evento</title>
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
      <h1>Editar Evento</h1>
      <form id="edit-event">
        <label>Título</label>
        <input id="titulo" name="titulo" value="{{ $event->titulo }}" />

        <label>Descripción</label>
        <textarea id="descripcion">{{ $event->descripcion }}</textarea>

        <label>Inicio</label>
        <input id="inicio" type="datetime-local" value="{{ optional($event->inicio)->format('Y-m-d\TH:i') }}" />

        <label>Fin</label>
        <input id="fin" type="datetime-local" value="{{ optional($event->fin)->format('Y-m-d\TH:i') }}" />

        <label>Lugar</label>
        <input id="ubicacion" value="{{ $event->ubicacion }}" />

        <label>Fecha recordatorio</label>
        <input id="fecha_recordatorio" type="datetime-local" value="{{ optional($event->fecha_recordatorio)->format('Y-m-d\TH:i') }}" />

        <p style="margin-top:0.75rem"><button type="submit">Guardar</button> <a href="/">Volver</a></p>
      </form>

      <pre id="output"></pre>
    </div>

    <script>
    (function(){
      const form=document.getElementById('edit-event'); const out=document.getElementById('output');
      form.addEventListener('submit', async (e)=>{ e.preventDefault(); out.textContent='Guardando...'; try{
        function toIso(v){ if(!v) return null; const d=new Date(v); return isNaN(d)? v.replace('T',' '): d.toISOString(); }
        const payload = {
          titulo: document.getElementById('titulo').value,
          descripcion: document.getElementById('descripcion').value,
          inicio: toIso(document.getElementById('inicio').value),
          fin: toIso(document.getElementById('fin').value),
          ubicacion: document.getElementById('ubicacion').value,
          fecha_recordatorio: toIso(document.getElementById('fecha_recordatorio').value)
        };
        const res = await fetch('/api/v1/events/{{ $event->id }}', { method: 'PUT', headers:{'Content-Type':'application/json','Accept':'application/json'}, body: JSON.stringify(payload)});
        const text = await res.text(); let data; try{data=JSON.parse(text)}catch(e){data=text}
        out.textContent = 'HTTP '+res.status+'\n'+JSON.stringify(data,null,2);
      }catch(err){ out.textContent='Error: '+(err.message||err); console.error(err) } });
    })();
    </script>
  </body>
</html>
