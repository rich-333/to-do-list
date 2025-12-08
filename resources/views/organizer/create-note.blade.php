<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Crear Nota</title>
    <style>
      .create-container { max-width:900px; margin:2rem auto; background:#fff; padding:1.25rem; border-radius:8px }
      input, textarea { width:100%; padding:0.5rem; border:1px solid #d6dde6; border-radius:6px }
      button { background:#0b74de; color:#fff; border:none; padding:0.5rem 0.8rem; border-radius:6px }
      .ai-btn { background:#9c27b0; color:#fff; border:none; padding:0.45rem 0.8rem; border-radius:6px; margin-right:8px }
    </style>
  </head>
  <body>
    <div class="create-container">
      <h2>Crear Nota</h2>
      <div>
        <label>Título</label>
        <input id="new-title" />
      </div>
      <div style="margin-top:0.75rem">
        <label>Contenido</label>
        <textarea id="new-content" rows="8"></textarea>
      </div>
      <div style="margin-top:0.5rem">
        <button id="analyze-create" class="ai-btn">🔍 Analizar</button>
        <button id="suggest-create" class="ai-btn" style="background:#ff9800;">🤖 Sugerir Items</button>
        <button id="expand-create" class="ai-btn">📖 Ampliar</button>
        <button id="summarize-create" class="ai-btn">📝 Resumir</button>
      </div>
      <div id="create-ai-status" style="margin-top:0.5rem;color:#666"></div>
      <div style="margin-top:0.75rem">
        <button id="save-create">Guardar Nota</button>
        <a href="/">Cancelar</a>
      </div>
    </div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const titleEl = document.getElementById('new-title');
  const contentEl = document.getElementById('new-content');
  const status = document.getElementById('create-ai-status');

  document.getElementById('analyze-create').addEventListener('click', async () => {
    status.textContent = '🔄 Analizando...';
    try {
      const res = await fetch('/api/v1/notes/ai/analyze', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl.value, content: contentEl.value }) });
      const data = await res.json();
      if (res.ok) {
        status.textContent = `Detectado: ${data.type} | Prioridad: ${data.priority} | ${data.word_count} palabras`;
      } else {
        status.textContent = 'Error: ' + (data.error || JSON.stringify(data));
      }
    } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
  });

  document.getElementById('suggest-create').addEventListener('click', async () => {
    status.textContent = '🔄 Generando sugerencias...';
    try {
      const res = await fetch('/api/v1/notes/ai/suggest', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl.value, content: contentEl.value }) });
      const data = await res.json();
      if (res.ok && data.items) {
        contentEl.value = contentEl.value + '\n\n' + data.items.map((it,i)=> (i+1)+'. '+it).join('\n');
        status.textContent = 'Sugerencias añadidas (' + (data.provider||'local') + ')';
      } else {
        status.textContent = 'Error: ' + (data.error || JSON.stringify(data));
      }
    } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
  });

  document.getElementById('expand-create').addEventListener('click', async () => {
    status.textContent = '🔄 Ampliando...';
    try {
      const res = await fetch('/api/v1/notes/ai/expand', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl.value, content: contentEl.value }) });
      const data = await res.json();
      if (res.ok && data.expanded) {
        contentEl.value = data.expanded;
        status.textContent = 'Contenido ampliado';
      } else {
        status.textContent = 'Error al ampliar';
      }
    } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
  });

  document.getElementById('summarize-create').addEventListener('click', async () => {
    status.textContent = '🔄 Resumiendo...';
    try {
      const res = await fetch('/api/v1/notes/ai/summarize', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl.value, content: contentEl.value }) });
      const data = await res.json();
      if (res.ok && data.summary) {
        contentEl.value = data.summary + '\n\n' + contentEl.value;
        status.textContent = 'Resumen añadido';
      } else {
        status.textContent = 'Error al resumir';
      }
    } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
  });

  document.getElementById('save-create').addEventListener('click', async (e) => {
    e.preventDefault();
    status.textContent = 'Guardando...';
    try {
      const payload = { titulo: titleEl.value, contenido: contentEl.value, etiquetas: [], color: null };
      const res = await fetch('/api/v1/notes', { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
      const data = await res.json();
      if (res.ok) {
        status.textContent = 'Nota creada';
        // ir al detalle de la nota
        const id = data.id || (data.data && data.data.id) || null;
        if (id) setTimeout(() => { location.href = '/organizer/notas/' + id; }, 600);
        else setTimeout(() => { location.href = '/'; }, 600);
      } else {
        status.textContent = 'Error guardando: ' + (data.message || JSON.stringify(data));
      }
    } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
  });
})();
</script>
  </body>
</html>
