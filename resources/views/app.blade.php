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
<body class="bg-main-primary box-border">
  <div id="root"></div>
</body>
</html>