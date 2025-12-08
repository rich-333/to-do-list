<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OrganizerAI - Tareas</title>
    @vite(['resources/css/app.css'])
    <style>
        [data-react-root] {
            display: contents;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-950">
   <div id="root"></div>

@viteReactRefresh
@vite(['resources/js/main.jsx'])
</body>
</html>
