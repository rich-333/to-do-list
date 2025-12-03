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
        <input name="color" id="color" value="{{ $note->color }}" />

        <p style="margin-top:0.75rem"><button type="submit">Guardar</button> <a href="/">Volver</a></p>
      </form>

      <pre id="output"></pre>
    </div>

    <script>
    (function(){
      const form = document.getElementById('edit-note');
      const out = document.getElementById('output');
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
    })();
    </script>
  </body>
</html>
