<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OrganizerAI - Tareas</title>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/Main.jsx'])
  </head>
  <body class="bg-gray-100 dark:bg-gray-950">
    <div id="root"></div>
  </body>
</html>
