<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>To-Do-List</title>

  @viteReactRefresh
  @vite([
    'resources/css/app.css',        
    'resources/js/Main.jsx'     
  ])
</head>
</head>
<body>
  <main style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; padding: 2rem;">
    <h1>OrganizerAI — Menú</h1>
    <p>Páginas de depuración (sin estilos):</p>
    <ul>
      <li><a href="/organizer">Índice</a></li>
      <li><a href="/organizer/notas">Notas</a></li>
      <li><a href="/organizer/tareas">Tareas</a></li>
      <li><a href="/organizer/eventos">Eventos</a></li>
    </ul>

    <section style="margin-top: 1.5rem;">
      <h2>Estado</h2>
      <ul>
        <li>API Notes: <code>/api/v1/notes</code> (POST/GET)</li>
        <li>API Tasks: <code>/api/v1/tasks</code> (POST/GET)</li>
        <li>API Events: <code>/api/v1/events</code> (POST/GET)</li>
      </ul>
    </section>
  </main>
</body>
</html>