<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>OrganizerAI - Notas (UI)</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111 }
      .container { max-width:900px; margin:2.5rem auto; background:#fff; padding:1.25rem 1.5rem; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.06); }
      form { display:block }
      .tag-list { list-style: none; padding: 0; display: flex; gap: 0.5rem; flex-wrap: wrap; margin:0.5rem 0 1rem 0 }
      .tag { background:#eee; padding:0.25rem 0.5rem; border-radius:999px; font-size:0.9rem; }
      .color-sample { width:40px; height:24px; display:inline-block; vertical-align:middle; border:1px solid #ccc; margin-left:0.5rem; }
      label { display:block; margin-top:0.75rem; font-weight:600 }
      input[type="text"], textarea { width:100%; max-width:520px; padding:0.4rem; border:1px solid #d6dde6; border-radius:6px }
      button { background:#0b74de; color:#fff; border:none; padding:0.6rem 0.9rem; border-radius:6px; cursor:pointer }
      button:hover { opacity:0.95 }
      pre { background:#f3f6f9; padding:0.75rem; border-radius:6px; max-width:900px; overflow:auto }
    </style>
  </head>
  <body>
    <div class="container">
    <h1>Notas — Interfaz (solo HTML)</h1>

    <p>Esta página muestra cómo funcionarán las <strong>etiquetas</strong> y el campo <strong>color</strong> en el formulario de creación de una nota.</p>

    <form id="create-note">
      <label for="titulo">Título</label>
      <input id="titulo" name="titulo" type="text" placeholder="Título de la nota" />

      <label for="contenido">Contenido</label>
      <textarea id="contenido" name="contenido" rows="4" placeholder="Escribe el cuerpo de la nota..."></textarea>

      <label for="etiquetas">Etiquetas (separadas por comas)</label>
      <input id="etiquetas" name="etiquetas" type="text" placeholder="ej: personal, trabajo, urgente" />

      <div style="margin-top:0.5rem;">
        <strong>Ejemplo de etiquetas:</strong>
        <ul class="tag-list">
          <li class="tag">personal</li>
          <li class="tag">trabajo</li>
          <li class="tag">urgente</li>
        </ul>
      </div>

      <label for="color">Color (hex)</label>
      <div>
        <input id="color" name="color" type="text" placeholder="#ffeb3b" style="width:110px;" />
        <span class="color-sample" style="background:#ffeb3b"></span>
      </div>

      <p style="margin-top:1rem; color:#444; max-width:640px;">Cómo lo procesará el backend: el campo <code>etiquetas</code> se convertirá en un arreglo JSON (ej. <code>["personal","trabajo"]</code>) y el campo <code>color</code> se guardará como cadena (ej. <code>#ffeb3b</code>).</p>

      <p style="margin-top:1rem;">
        <button type="submit">Crear nota</button>
      </p>
    </form>

    <pre id="output"></pre>

    <script>
    (function(){
      const form = document.getElementById('create-note');
      const out = document.getElementById('output');
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        out.textContent = 'Enviando...';
        try {
          const fd = new FormData(form);
          const etiquetasRaw = fd.get('etiquetas') || '';
          const etiquetas = etiquetasRaw ? etiquetasRaw.split(',').map(s => s.trim()).filter(Boolean) : [];
          const payload = {
            titulo: fd.get('titulo'),
            contenido: fd.get('contenido'),
            etiquetas: etiquetas,
            color: fd.get('color') || null,
            usuario_id: 1
          };

          const res = await fetch('/api/v1/notes', {
            method: 'POST',
            headers: {'Content-Type':'application/json', 'Accept':'application/json'},
            body: JSON.stringify(payload)
          });

          const text = await res.text();
          let data;
          try { data = JSON.parse(text); } catch (err) { data = text; }
          out.textContent = 'HTTP ' + res.status + '\n' + JSON.stringify(data, null, 2);
        } catch (err) {
          out.textContent = 'Error: ' + (err.message || err);
          console.error(err);
        }
      });
    })();
    </script>
    </div>
  </body>
</html>
