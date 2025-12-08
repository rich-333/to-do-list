// === LÓGICA DE PESTAÑAS ===

let activeTab = 'notes';

function switchTab(tab) {
  activeTab = tab;
  document.querySelectorAll('.tab-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.nav-pill').forEach(p => {
    p.style.background = 'transparent';
    p.style.color = '#111';
    p.style.borderColor = '#111';
  });

  const pane = document.getElementById('tab-' + tab);
  if (pane) pane.style.display = 'block';

  const pill = document.querySelector(`.nav-pill[data-tab="${tab}"]`);
  if (pill) {
    pill.style.background = '#111';
    pill.style.color = '#fff';
    pill.style.borderColor = '#111';
  }
}

function onAddClick() {
  // Open the inline create note form inside the NOTAS tab
  switchTab('notes');
  if (typeof showInlineCreateNote === 'function') showInlineCreateNote();
}

// Inicializar pestañas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
  // Mostrar la pestaña de notas por defecto
  switchTab('notes');
});

// Attach handlers for task checklist toggles (delegated)
document.addEventListener('change', async function(e) {
  if (!e.target.matches('.subtask-checkbox-task')) return;
  const cb = e.target;
  const taskId = cb.getAttribute('data-task-id');
  if (!taskId) return;

  // build checklist from DOM for this task
  const container = cb.closest('.note-box');
  const checkboxes = Array.from(container.querySelectorAll('.subtask-checkbox-task'));
  const titles = Array.from(container.querySelectorAll('.subtask-title')).map(s => s.textContent.trim());
  const checklist = checkboxes.map((c, i) => ({ title: titles[i] || '', completed: c.checked }));

  try {
    const res = await fetch('/api/v1/tasks/' + taskId, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
      body: JSON.stringify({ subtareas: checklist })
    });
    if (!res.ok) {
      console.error('Failed to update task checklist', await res.text());
    }
  } catch (err) {
    console.error('Error updating checklist:', err);
  }
});
