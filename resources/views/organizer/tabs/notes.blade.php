<div id="tab-notes" class="tab-pane">
  <div id="inline-create-note" style="display:none; margin-bottom:1rem;">
    <div style="padding:14px; border:2px dashed #e0e0e0; border-radius:8px; background:#fff;">
      <div style="display:flex; gap:12px; align-items:center; justify-content:space-between;">
        <strong>Crear Nota</strong>
        <div>
          <a href="#" onclick="hideInlineCreateNote();return false;" style="color:#d32f2f; text-decoration:none;">Cancelar</a>
        </div>
      </div>

        <script>
        // Inline create note helpers
        function showInlineCreateNote() {
          switchTab('notes');
          const box = document.getElementById('inline-create-note');
          if (!box) return;
          box.style.display = 'block';
          setTimeout(() => { document.getElementById('new-title-inline')?.focus(); }, 100);
        }
        function hideInlineCreateNote() {
          const box = document.getElementById('inline-create-note');
          if (!box) return;
          box.style.display = 'none';
        }

        (function initInlineCreate(){
          console.log('[notes] initializing inline create handlers (initInlineCreate)');
          const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
          const attach = () => {
            // Query elements at attach time (they may not exist when the script first runs)
            const titleEl = document.getElementById('new-title-inline');
            const contentEl = document.getElementById('new-content-inline');
            const status = document.getElementById('create-ai-status-inline');

            const analyzeBtn = document.getElementById('analyze-create-inline');
            const suggestBtn = document.getElementById('suggest-create-inline');
            const expandBtn = document.getElementById('expand-create-inline');
            const summarizeBtn = document.getElementById('summarize-create-inline');
            const saveBtn = document.getElementById('save-create-inline');

            if (!analyzeBtn) console.warn('[notes] analyze button not found at attach');
            if (!suggestBtn) console.warn('[notes] suggest button not found at attach');
            if (!expandBtn) console.warn('[notes] expand button not found at attach');
            if (!summmarizeBtn) console.warn('[notes] summarize button not found at attach');
            if (!saveBtn) console.warn('[notes] save button not found at attach');

            analyzeBtn?.addEventListener('click', async () => {
              if (!status) return; status.textContent = '🔄 Analizando...';
              try {
                const res = await fetch('/api/v1/notes/ai/analyze', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl?.value, content: contentEl?.value }) });
                const data = await res.json();
                if (res.ok) {
                  status.textContent = `Detectado: ${data.type} | Prioridad: ${data.priority} | ${data.word_count} palabras`;
                } else {
                  status.textContent = 'Error: ' + (data.error || JSON.stringify(data));
                }
              } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
            });

            suggestBtn?.addEventListener('click', async () => {
              if (!status) return; status.textContent = '🔄 Generando sugerencias...';
              try {
                const res = await fetch('/api/v1/notes/ai/suggest', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl?.value, content: contentEl?.value }) });
                const text = await res.text();
                let data;
                try { data = JSON.parse(text); } catch(e) { data = null; }
                if (res.ok && data && data.items) {
                  contentEl.value = contentEl.value + '\n\n' + data.items.map((it,i)=> (i+1)+'. '+it).join('\n');
                  const prov = data.provider || 'local';
                  status.textContent = 'Sugerencias añadidas (' + prov + ')';
                  const badge = document.getElementById('create-ai-provider-badge-inline');
                  if (badge) { badge.textContent = 'IA: ' + prov; badge.style.display = 'inline-block'; }
                } else {
                  status.textContent = 'Error: ' + (data?.error || text.slice(0,300));
                }
              } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
            });

            expandBtn?.addEventListener('click', async () => {
              if (!status) return; status.textContent = '🔄 Ampliando...';
              try {
                const res = await fetch('/api/v1/notes/ai/expand', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl?.value, content: contentEl?.value }) });
                const data = await res.json();
                if (res.ok && data.expanded) {
                  contentEl.value = data.expanded;
                  const prov = data.provider || 'local';
                  status.textContent = 'Contenido ampliado (' + prov + ')';
                  const badge = document.getElementById('create-ai-provider-badge-inline');
                  if (badge) { badge.textContent = 'IA: ' + prov; badge.style.display = 'inline-block'; }
                } else {
                  status.textContent = 'Error al ampliar';
                }
              } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
            });

            summarizeBtn?.addEventListener('click', async () => {
              if (!status) return; status.textContent = '🔄 Resumiendo...';
              try {
                const res = await fetch('/api/v1/notes/ai/summarize', { method: 'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify({ title: titleEl?.value, content: contentEl?.value }) });
                const data = await res.json();
                if (res.ok && data.summary) {
                  contentEl.value = data.summary + '\n\n' + contentEl.value;
                  const prov = data.provider || 'local';
                  status.textContent = 'Resumen añadido (' + prov + ')';
                  const badge = document.getElementById('create-ai-provider-badge-inline');
                  if (badge) { badge.textContent = 'IA: ' + prov; badge.style.display = 'inline-block'; }
                } else {
                  status.textContent = 'Error al resumir';
                }
              } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
            });

            saveBtn?.addEventListener('click', async (e) => {
              e.preventDefault();
              if (!status) return; status.textContent = 'Guardando...';
              try {
                const payload = { titulo: titleEl?.value, contenido: contentEl?.value, etiquetas: [], color: null };
                const res = await fetch('/api/v1/notes', { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': csrf }, body: JSON.stringify(payload) });
                const data = await res.json();
                if (res.ok) {
                  status.textContent = 'Nota creada';
                  const id = data.id || (data.data && data.data.id) || null;
                  if (id) setTimeout(() => { location.href = '/organizer/notas/' + id; }, 600);
                  else setTimeout(() => { location.reload(); }, 600);
                } else {
                  status.textContent = 'Error guardando: ' + (data.message || JSON.stringify(data));
                }
              } catch (e) { status.textContent = 'Error: '+(e.message||e); console.error(e) }
            });
          };

          // Si el DOM ya está listo, adjuntar inmediatamente, sino esperar al evento
          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attach);
          } else {
            attach();
          }

        })();
        </script>
      <div style="margin-top:8px">
        <input id="new-title-inline" placeholder="Título" style="width:100%; padding:8px; border:1px solid #e6e6e6; border-radius:6px" />
      </div>
      <div style="margin-top:8px">
        <textarea id="new-content-inline" rows="6" placeholder="Contenido" style="width:100%; padding:8px; border:1px solid #e6e6e6; border-radius:6px"></textarea>
      </div>
      <div style="margin-top:8px; display:flex; gap:8px; align-items:center;">
        <button id="analyze-create-inline" class="ai-btn" style="background:#9c27b0;color:#fff;border:none;padding:8px 10px;border-radius:6px">🔍 Analizar</button>
        <button id="suggest-create-inline" class="ai-btn" style="background:#ff9800;color:#fff;border:none;padding:8px 10px;border-radius:6px">🤖 Sugerir Items</button>
        <button id="expand-create-inline" class="ai-btn" style="background:#8bc34a;color:#fff;border:none;padding:8px 10px;border-radius:6px">📖 Ampliar</button>
        <button id="summarize-create-inline" class="ai-btn" style="background:#607d8b;color:#fff;border:none;padding:8px 10px;border-radius:6px">📝 Resumir</button>
        <button id="save-create-inline" style="margin-left:auto; background:#0b74de; color:#fff; border:none; padding:8px 12px; border-radius:6px">Guardar</button>
      </div>
      <div style="margin-top:8px; display:flex; gap:12px; align-items:center;">
        <div id="create-ai-status-inline" style="color:#666"></div>
        <div id="create-ai-provider-badge-inline" style="display:none; padding:4px 8px; border-radius:12px; background:#eef; color:#124; font-size:12px; font-weight:600">IA: local</div>
      </div>
    </div>
  </div>

  <div id="notes-list">
    @php $colors = ['#f7d36c','#5cc27a','#ffb36b']; @endphp
    @if(isset($notas) && $notas->count())
        @foreach($notas as $i => $n)
          @php $c = $n->color ?? $colors[$i % count($colors)]; @endphp
        <a href="/organizer/notas/{{ $n->id }}" style="text-decoration:none; color:inherit">
          <div class="note-box" style="border:4px solid {{ $c }};">
            <div class="note-title">{{ $n->titulo ?? 'Nota' }}</div>
          </div>
        </a>
      @endforeach
    @else
      <div style="color:#666; padding:22px; border:2px dashed #ddd; border-radius:6px">No hay notas aún.</div>
    @endif
  </div>
</div>
