<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>OrganizerAI - Debug</title>
  </head>
  <head>
    <meta charset="utf-8">
    <title>OrganizerAI - Debug</title>
    <style>
      body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; background:#f7f9fb; color:#111; }
      .container { max-width: 900px; margin: 2.5rem auto; background: #fff; padding: 1.25rem 1.5rem; border-radius:8px; box-shadow:0 6px 18px rgba(18,24,36,0.06); }
      a { color:#0b74de; text-decoration:none }
      a:hover { text-decoration:underline }
      h1 { margin-top:0 }
      ul { padding-left:1rem }
    </style>
  </head>
  <body>
    <div class="container">
      <h1>OrganizerAI — Debug</h1>
      <nav style="margin-bottom:1rem">
        <a href="/organizer/notas">Notas</a> ·
        <a href="/organizer/tareas">Tareas</a> ·
        <a href="/organizer/eventos">Eventos</a>
      </nav>

      <section style="display:flex; gap:1rem; align-items:flex-start;">
        <div style="flex:1;">
          <h2 style="margin-bottom:0.4rem">Últimas notas</h2>
          @if(isset($notas) && $notas->count())
            <ul style="padding-left:0; list-style:none; margin:0">
              @foreach($notas as $n)
                <li style="padding:0.45rem 0; border-bottom:1px solid #f0f2f5; display:flex; gap:0.6rem; align-items:center">
                  <span aria-hidden style="font-size:1.2rem">📝</span>
                  <div>
                    <div style="font-weight:600"><a href="/organizer/notas/{{ $n->id }}">{{ $n->titulo }}</a></div>
                    <div style="font-size:0.9rem; color:#555">{{ Str::limit($n->contenido ?? '', 80) }}</div>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div style="color:#666">No hay notas aún.</div>
          @endif
        </div>

        <div style="flex:1;">
          <h2 style="margin-bottom:0.4rem">Últimas tareas</h2>
          @if(isset($tareas) && $tareas->count())
            <ul style="padding-left:0; list-style:none; margin:0">
              @foreach($tareas as $t)
                <li style="padding:0.45rem 0; border-bottom:1px solid #f0f2f5; display:flex; gap:0.6rem; align-items:center">
                  <span aria-hidden style="font-size:1.2rem">✅</span>
                  <div>
                    <div style="font-weight:600"><a href="/organizer/tareas/{{ $t->id }}">{{ $t->titulo }}</a></div>
                    <div style="font-size:0.9rem; color:#555">Conjunto: {{ $t->conjunto ?? '-' }} • {{ implode(', ', $t->etiquetas ?? []) }}</div>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div style="color:#666">No hay tareas aún.</div>
          @endif
        </div>

        <div style="flex:1;">
          <h2 style="margin-bottom:0.4rem">Próximos eventos</h2>
          @if(isset($eventos) && $eventos->count())
            <ul style="padding-left:0; list-style:none; margin:0">
              @foreach($eventos as $e)
                <li style="padding:0.45rem 0; border-bottom:1px solid #f0f2f5; display:flex; gap:0.6rem; align-items:center">
                  <span aria-hidden style="font-size:1.2rem">📅</span>
                  <div>
                    <div style="font-weight:600"><a href="/organizer/eventos/{{ $e->id }}">{{ $e->titulo }}</a></div>
                    <div style="font-size:0.9rem; color:#555">{{ optional($e->inicio)->format('Y-m-d H:i') ?? '-' }} • {{ $e->ubicacion ?? '' }}</div>
                  </div>
                </li>
              @endforeach
            </ul>
          @else
            <div style="color:#666">No hay eventos próximos.</div>
          @endif
        </div>
      </section>
    </div>
  </body>
</html>
