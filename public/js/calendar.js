// === CALENDARIO ===

// Agrupar eventos por fecha para modal de día
const EVENTS_BY_DATE = {};

// Esta función se ejecutará después de que el servidor inyecte los eventos
function populateEventsMap(jsEvents) {
  jsEvents.forEach(ev => {
    if (!empty(ev.inicio)) {
      const dateKey = new Date(ev.inicio).toISOString().split('T')[0];
      if (!EVENTS_BY_DATE[dateKey]) EVENTS_BY_DATE[dateKey] = [];
      EVENTS_BY_DATE[dateKey].push(ev);
    }
  });
}

function empty(value) {
  return !value || value === null || value === undefined;
}

function openDayEventsModal(year, month, day) {
  const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
  const events = EVENTS_BY_DATE[dateStr] || [];
  // Ordenar por hora
  events.sort((a, b) => new Date(a.inicio).getTime() - new Date(b.inicio).getTime());
  
  const container = document.getElementById('day-events-modal-container');
  let eventsHtml = events.map(ev => {
    const startTime = new Date(ev.inicio).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    const color = ev.color || '#c8e7ff';
    return `
      <div style="padding: 12px; margin-bottom: 8px; border-left: 4px solid ${color}; background: #f9f9f9; border-radius: 4px;">
        <div style="font-weight: 600; color: #333;">${ev.titulo}</div>
        <div style="font-size: 13px; color: #666; margin-top: 4px;">🕒 ${startTime}</div>
        ${ev.descripcion ? `<div style="font-size: 13px; color: #888; margin-top: 4px;">${ev.descripcion}</div>` : ''}
      </div>
    `;
  }).join('');
  
  // Pre-fill datetime with the selected date at 09:00
  const prefilledDateTime = `${dateStr}T09:00`;
  
  container.innerHTML = `
    <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
      <div style="background: white; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h2 style="margin-top: 0; margin-bottom: 16px; color: #333;">Eventos del ${day} de ${month} de ${year}</h2>
        <div style="margin-bottom: 24px; min-height: 100px;">
          ${eventsHtml || '<div style="color: #999; text-align: center; padding: 32px;">No hay eventos este día</div>'}
        </div>
        <button onclick="closeDayEventsModal(); renderQuickAddModal('calendar', '${prefilledDateTime}');" style="width: 100%; padding: 10px; background: #4a8f3a; color: white; border: none; border-radius: 4px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 8px;">+ Agregar evento</button>
        <button onclick="closeDayEventsModal()" style="width: 100%; padding: 10px; background: #e0e0e0; color: #333; border: none; border-radius: 4px; font-size: 14px; cursor: pointer;">Cerrar</button>
      </div>
    </div>
  `;
}

function closeDayEventsModal() {
  document.getElementById('day-events-modal-container').innerHTML = '';
}
