# OrganizerAI — API & Uso rápido

Base URL: /api/v1

Autenticación: El proyecto usa autenticación tradicional (sessions). Para pruebas rápidas, los endpoints aceptan `usuario_id` en la carga si no estás autenticado.

## Notas (Notes)
- GET /api/v1/notes — lista (opcional query: usuario_id, etiquetas=tag1,tag2)
- POST /api/v1/notes — crear (payload: titulo, contenido, etiquetas[], color, usuario_id)
- GET /api/v1/notes/{id}
- PUT/PATCH /api/v1/notes/{id}
- DELETE /api/v1/notes/{id}
- POST /api/v1/notes/{id}/summarize — devuelve un resumen (stub)
- POST /api/v1/notes/transform — transforma texto (operación: uppercase|lowercase|striphtml)
- POST /api/v1/notes/{id}/to-task — convierte nota en tarea

## Tareas (Tasks)
- GET /api/v1/tasks — lista (filtros: prioridad, estado, etiquetas, usuario_id)
- POST /api/v1/tasks — crear
- GET /api/v1/tasks/{id}
- PUT/PATCH /api/v1/tasks/{id}
- DELETE /api/v1/tasks/{id}
- POST /api/v1/tasks/{id}/complete — marcar completada

## Calendario (Events)
- GET /api/v1/events
- POST /api/v1/events — payload: titulo, descripcion, inicio, fin, ubicacion, fecha_recordatorio, usuario_id
- POST o PATCH con fecha_recordatorio programará un job en la cola para enviar el recordatorio

## Configuración de correo y colas
- Para enviar recordatorios reales: configurar .env (MAIL_*, MAIL_MAILER) y driver de colas: QUEUE_CONNECTION=database (ej.)
- Crear tabla de jobs (ya incluida) y ejecutar worker: php artisan queue:work --tries=3

## Vista de prueba (minimal)
- /organizer — index con links a formularios de prueba
- /organizer/notes — crear nota via fetch
- /organizer/tasks — crear tarea
- /organizer/events — crear evento y programar recordatorio
