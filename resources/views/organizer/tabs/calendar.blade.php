<div id="tab-calendar" class="tab-pane" style="display:none">
  <?php
    // Prepare events for frontend calendar
    $jsEvents = collect([]);
    if (isset($eventos) && $eventos->count()) {
      $jsEvents = $eventos->map(function($e){
        return [
          'id' => $e->id,
          'titulo' => $e->titulo,
          'inicio' => optional($e->inicio)->toDateTimeString(),
          'fin' => optional($e->fin)->toDateTimeString(),
          'color' => $e->color ?? null,
        ];
      });
    }
  ?>

  <div style="display:flex; gap:16px; align-items:flex-start;">
    <div style="flex:1;">
      <div id="cal-grid" style="display:grid; 
           grid-template-columns: repeat(7, 1fr); 
          gap:6px; margin-top:12px;">
        </div>
        <div style="margin-bottom:12px;">
          <div style="background:#6aa84f; color:#fff; padding:10px 12px; border-radius:8px; text-align:center; font-weight:800; font-size:18px;"> 
            <span id="cal-month" style="letter-spacing:1px"></span>
          </div>
          <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
            <div style="display:flex; gap:8px; align-items:center;">
              <button id="cal-prev" style="padding:6px 10px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer;">◀</button>
              <button id="cal-next" style="padding:6px 10px; border-radius:6px; border:1px solid #ddd; background:#fff; cursor:pointer;">▶</button>
            </div>
            <div style="font-size:13px; color:#666">Mes</div>
          </div>
        </div>
        <div style="margin-top:8px;">
          <?php
            // Render calendar as HTML table server-side for guaranteed grid visibility
            $now = \Carbon\Carbon::now();
            $first = $now->copy()->startOfMonth();
            $startOffset = ($first->dayOfWeek + 6) % 7; // Monday=0
            $daysInMonth = $first->daysInMonth;
            $weeks = ceil(($startOffset + $daysInMonth) / 7);
            $weekdays = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
          ?>

          <table style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <thead>
              <tr>
                <?php foreach($weekdays as $wd): ?>
                  <th style="background:#6aa84f; color:#fff; padding:10px 6px; font-weight:800; border:2px solid #6aa84f;"><?php echo $wd; ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <?php for($w=0;$w<$weeks;$w++): ?>
                <tr>
                  <?php for($d=0;$d<7;$d++):
                    $cellIndex = $w*7 + $d - $startOffset + 1;
                    if ($cellIndex < 1 || $cellIndex > $daysInMonth) {
                      echo '<td style="height:92px; border:2px solid #6aa84f; vertical-align:top; background:#fff;"></td>';
                    } else {
                      $cellDate = $first->copy()->day($cellIndex);
                      $isToday = $cellDate->isSameDay($now);
                      $dow = $cellDate->dayOfWeek; // 0 Sunday .. 6 Saturday
                      $style = 'height:92px; border:2px solid #6aa84f; vertical-align:top; padding:8px; cursor:pointer; transition:box-shadow 0.2s;';
                      if ($dow === 6) $style .= ' background:#f4e7c2;';
                      if ($dow === 0) $style .= ' background:#e6e6e6;';
                      if ($isToday) $style .= ' background:#fff1b8; border:3px solid #4a8f3a;';
                      $dateStr = $cellDate->format('Y-m-d');
                      echo '<td class="cal-cell" data-date="' . $dateStr . '" style="' . $style . '" onmouseover="this.style.boxShadow=\'inset 0 0 8px rgba(106,168,79,0.3)\'" onmouseout="this.style.boxShadow=\'\';">';
                      echo '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;"><div style="font-weight:700">' . $cellIndex . '</div></div>';

                      // events for the day
                      $evCount = 0;
                      foreach($jsEvents as $ev) {
                        if (empty($ev['inicio'])) continue;
                        $evDate = (new DateTime($ev['inicio']))->format('Y-m-d');
                        if ($evDate === $cellDate->format('Y-m-d')) {
                          $bg = $ev['color'] ?? '#f0f6ff';
                          $titulo = htmlspecialchars($ev['titulo'] ?? '');
                          echo "<a href=\"/organizer/eventos/" . ($ev['id'] ?? '') . "\" style=\"display:block; padding:6px 8px; margin-bottom:6px; border-radius:6px; text-decoration:none; color:#111; background:{$bg};\">{$titulo}</a>";
                          $evCount++; if ($evCount >= 3) break;
                        }
                      }

                      echo '</td>';
                    }
                  endfor; ?>
                </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div style="width:320px;">
      <div style="background:#fff; padding:14px; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.04);">
        <h3 style="margin-top:0; margin-bottom:8px;">Eventos próximos</h3>
        <div id="upcoming-events">
          @if(isset($eventos) && $eventos->count())
            @foreach($eventos as $e)
              <?php
                $now = \Carbon\Carbon::now();
                $eventDate = optional($e->inicio);
                $timeLabel = '';
                if ($eventDate) {
                  $diff = (int)$eventDate->diffInDays($now); // absolute difference in days
                  $isFuture = $eventDate->isFuture();
                  if ($diff === 0) $timeLabel = '(Hoy)';
                  elseif ($diff === 1 && $isFuture) $timeLabel = '(Mañana)';
                  elseif ($diff > 1 && $isFuture) $timeLabel = "(en $diff días)";
                  elseif ($diff > 0 && !$isFuture) $timeLabel = "(hace $diff días)";
                }
              ?>
              <div style="padding:8px 6px; border-bottom:1px solid #f0f0f0;">
                <div style="font-weight:600">{{ $e->titulo }}</div>
                <div style="font-size:13px; color:#666">{{ optional($e->inicio)->format('Y-m-d H:i') }} {{ $timeLabel }}</div>
              </div>
            @endforeach
          @else
            <div style="color:#666">No hay eventos próximos.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    const SERVER_EVENTS = @json($jsEvents->values());
    
    // Populate the events map for day modal
    if (typeof populateEventsMap === 'function') {
      populateEventsMap(SERVER_EVENTS);
    }

    // Build EVENTS_BY_DATE map from SERVER_EVENTS for modal filtering
    if (typeof window.EVENTS_BY_DATE === 'undefined') {
      window.EVENTS_BY_DATE = {};
    }
    SERVER_EVENTS.forEach(ev => {
      if (!ev.inicio) return;
      const d = new Date(ev.inicio);
      const key = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
      if (!window.EVENTS_BY_DATE[key]) window.EVENTS_BY_DATE[key] = [];
      window.EVENTS_BY_DATE[key].push(ev);
    });

    // Function to open day events modal with events filtered by date
    function openDayEventsModal(year, month, day) {
      console.log('[openDayEventsModal] Called with:', { year, month, day });
      const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      console.log('[openDayEventsModal] dateStr:', dateStr);
      const events = window.EVENTS_BY_DATE[dateStr] || [];
      console.log('[openDayEventsModal] events found:', events.length);
      // Sort by time
      events.sort((a, b) => new Date(a.inicio).getTime() - new Date(b.inicio).getTime());
      
      const container = document.getElementById('day-events-modal-container');
      let eventsHtml = events.map(ev => {
        const startTime = new Date(ev.inicio).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
        const color = ev.color || '#c8e7ff';
        return `
          <div style="padding: 12px; margin-bottom: 8px; border-left: 4px solid ${color}; background: #f9f9f9; border-radius: 4px;">
            <div style="font-weight: 600; color: #333;">
              <a href="/organizer/eventos/${ev.id || ''}" style="text-decoration: none; color: #333; cursor: pointer;">${ev.titulo || 'Sin título'}</a>
            </div>
            <div style="font-size: 13px; color: #666; margin-top: 4px;">🕒 ${startTime}</div>
          </div>
        `;
      }).join('');
      
      // Pre-fill datetime with the selected date at 09:00
      const prefilledDateTime = `${dateStr}T09:00`;
      
      // Get month name in Spanish
      const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
      const monthName = monthNames[month - 1] || month;
      console.log('[openDayEventsModal] monthName:', monthName, 'from index:', month - 1);
      
      container.innerHTML = `
        <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 2000;">
          <div style="background: white; border-radius: 8px; padding: 24px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h2 style="margin-top: 0; margin-bottom: 16px; color: #333;">Eventos del ${day} de ${monthName} de ${year}</h2>
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
    
    (function(){
      // Calendar state
      let current = new Date();
      // Start week on Monday
      const weekdayNames = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];

      function startOfMonth(d){ return new Date(d.getFullYear(), d.getMonth(), 1); }
      function endOfMonth(d){ return new Date(d.getFullYear(), d.getMonth()+1, 0); }

      function renderCalendar(date){
        const root = document.getElementById('cal-grid');
        const monthLabel = document.getElementById('cal-month');
        root.innerHTML = '';
        const year = date.getFullYear();
        const month = date.getMonth();
        monthLabel.textContent = date.toLocaleString('default', { month: 'long', year: year }).toUpperCase();

        // header row with weekdays (green)
        weekdayNames.forEach(w => {
          const h = document.createElement('div');
          h.style.fontWeight = '700'; h.style.padding = '8px 6px'; h.style.textAlign = 'center'; h.style.background = '#6aa84f'; h.style.color = '#fff'; h.style.borderRadius = '6px';
          h.textContent = w;
          root.appendChild(h);
        });

        const s = startOfMonth(date);
        // get index of first day in Monday-start grid
        let firstDayIndex = s.getDay(); // 0 Sun .. 6 Sat
        // convert to Monday-start index
        firstDayIndex = (firstDayIndex + 6) % 7; // 0=Mon .. 6=Sun

        const totalDays = endOfMonth(date).getDate();
        // fill blanks before first day
        for(let i=0;i<firstDayIndex;i++){
          const cell = document.createElement('div');
          cell.style.minHeight = '80px'; cell.style.padding='6px'; cell.style.borderRadius='6px'; cell.style.background='#fff';
          root.appendChild(cell);
        }

        // map events by date
        const evMap = {};
        SERVER_EVENTS.forEach(ev => {
          if (!ev.inicio) return;
          const d = new Date(ev.inicio);
          const key = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
          evMap[key] = evMap[key] || [];
          evMap[key].push(ev);
        });

        for(let day=1; day<=totalDays; day++){
          const cell = document.createElement('div');
          cell.style.minHeight = '80px'; cell.style.padding='8px'; cell.style.borderRadius='6px'; cell.style.background='#fff'; cell.style.border='1px solid #6aa84f';
          // shade weekends: Saturday (6) beige, Sunday (0) light gray
          const cellDate = new Date(year, month, day);
          const dow = cellDate.getDay();
          if (dow === 6) { // Saturday
            cell.style.background = '#f4e7c2';
          } else if (dow === 0) { // Sunday
            cell.style.background = '#e6e6e6';
          }
          // highlight today
          const today = new Date();
          if (today.getFullYear() === cellDate.getFullYear() && today.getMonth() === cellDate.getMonth() && today.getDate() === cellDate.getDate()) {
            cell.style.background = '#fff1b8';
            cell.style.border = '2px solid #4a8f3a';
          }
          const dateStr = year + '-' + String(month+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
          const top = document.createElement('div'); top.style.display='flex'; top.style.justifyContent='space-between'; top.style.alignItems='center'; top.style.marginBottom='6px';
          const dn = document.createElement('div'); dn.style.fontWeight='700'; dn.textContent = day;
          top.appendChild(dn);
          cell.appendChild(top);

          const evs = evMap[dateStr] || [];
          evs.slice(0,3).forEach(ev => {
            const b = document.createElement('a');
            b.href = '/organizer/eventos/' + (ev.id || '');
            b.style.display='block'; b.style.padding='6px 8px'; b.style.marginBottom='6px'; b.style.borderRadius='6px'; b.style.textDecoration='none'; b.style.color='#111';
            b.style.background = ev.color || '#f0f6ff';
            b.textContent = ev.titulo || '';
            cell.appendChild(b);
          });

          // Add click listener to open day modal
          cell.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' || e.target.closest('a')) return; // Don't open if clicking event link
            openDayEventsModal(year, month + 1, day); // month + 1 because JS months are 0-11
          });

          root.appendChild(cell);
        }
      }

      document.getElementById('cal-prev').addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()-1, 1); renderCalendar(current); });
      document.getElementById('cal-next').addEventListener('click', () => { current = new Date(current.getFullYear(), current.getMonth()+1, 1); renderCalendar(current); });

      // initial render
      renderCalendar(current);
    })();
  </script>
</div>
