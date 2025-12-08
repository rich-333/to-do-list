// === BÚSQUEDA SEMÁNTICA GLOBAL ===

(function(){
  const searchInput = document.getElementById('global-search');
  const resultsDiv = document.getElementById('search-results');
  const csrf = document.querySelector('meta[name="csrf-token"]').content || '';
  let searchTimeout;

  if (!searchInput) return;

  searchInput.addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    const query = e.target.value.trim();

    if (query.length < 2) {
      resultsDiv.style.display = 'none';
      return;
    }

    searchTimeout = setTimeout(() => performSearch(query), 300);
  });

  // Cerrar resultados al perder el foco
  searchInput.addEventListener('blur', () => {
    setTimeout(() => { resultsDiv.style.display = 'none'; }, 200);
  });

  searchInput.addEventListener('focus', () => {
    if (searchInput.value.trim().length >= 2) {
      resultsDiv.style.display = 'block';
    }
  });

  async function performSearch(query) {
    try {
      console.log('[search.js] Buscando:', query);
      const res = await fetch(`/search?q=${encodeURIComponent(query)}&provider=groq`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) {
        console.error('[search.js] Error en búsqueda, status:', res.status);
        resultsDiv.innerHTML = '<div style="padding:12px; color:#c62828;">Error en búsqueda</div>';
        resultsDiv.style.display = 'block';
        return;
      }

      const { results, provider } = await res.json();
      console.log('[search.js] Resultados de ' + provider + ':', results);

      if (results.length === 0) {
        resultsDiv.innerHTML = '<div style="padding:12px; color:#999;">Sin resultados</div>';
      } else {
        resultsDiv.innerHTML = results.map(r => `
          <div style="padding:12px; border-bottom:1px solid #f0f0f0; cursor:pointer; hover:background:#f5f5f5;" onclick="selectResult('${r.id}', '${r.type}')">
            <div style="font-weight:600; font-size:14px;">${escapeHtml(r.title)}</div>
            <div style="font-size:12px; color:#666; margin-top:4px;">${escapeHtml(r.preview)}</div>
            ${r.list ? '<div style="font-size:11px; color:#0b74de; margin-top:4px;">📋 ' + escapeHtml(r.list) + '</div>' : ''}
          </div>
        `).join('');
      }

      resultsDiv.style.display = 'block';
    } catch (e) {
      console.error('[search.js] Error:', e);
      resultsDiv.innerHTML = '<div style="padding:12px; color:#c62828;">Error: ' + e.message + '</div>';
      resultsDiv.style.display = 'block';
    }
  }

  window.selectResult = function(resultId, type) {
    console.log('[search.js] Seleccionado:', resultId, type);
    // Aquí se puede implementar navegación según el tipo
    if (type === 'note') {
      alert('Navegar a nota: ' + resultId);
    } else if (type === 'item') {
      alert('Navegar a item de lista: ' + resultId);
    }
    resultsDiv.style.display = 'none';
  };

  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
  }
})();
