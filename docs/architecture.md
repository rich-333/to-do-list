# OrganizerAI — Diseño de base de datos y API (backend)

Resumen: Este documento describe las tablas MySQL, relaciones Eloquent y los endpoints API para los módulos backend: Notas, Tareas y Calendario. El frontend será mínimo — unas vistas HTML para probar el backend.

## Principios
- Usar MySQL como almacenamiento principal
- Laravel Eloquent para modelos y relaciones
- Colas (queue) para envío de recordatorios por correo
- Tags como campo JSON en primera fase (simplifica el modelo) — fácil de migrar a tabla pivot si se requiere

## Tablas propuestas

1) notes
- id (PK)
- usuario_id (FK -> users.id)
- titulo (string)
- contenido (text)
- etiquetas (json) — array de strings
- color (string|null)
- created_at, updated_at

2) tasks
- id (PK)
- usuario_id (FK -> users.id)
- titulo (string)
- descripcion (text|null)
- prioridad (enum: baja, media, alta)
- fecha_limite (datetime|null)
- estado (enum: pendiente, en_progreso, completada, cancelada)
- etiquetas (json)
- fecha_completada (datetime|null)
- created_at, updated_at

3) events
- id (PK)
- usuario_id (FK -> users.id)
- titulo (string)
- descripcion (text|null)
- inicio (datetime)
- fin (datetime|null)
- ubicacion (string|null)
- fecha_recordatorio (datetime|null)  # Cuando debería dispararse el recordatorio por correo
- created_at, updated_at

## API Endpoints (REST)

Notas (Notes)
- GET /api/notes — listar notas (con filtro por usuario, etiquetas, color)
- POST /api/notes — crear nota
- GET /api/notes/{id} — ver nota
- PUT/PATCH /api/notes/{id} — actualizar
- DELETE /api/notes/{id} — eliminar

IA adicionales (endpoints simple placeholder)
- POST /api/notes/{id}/summarize — devuelve resumen
- POST /api/notes/transform — aplicar transformación de texto (stub)

Tareas (Tasks)
- GET /api/tasks — listar (filtros: prioridad, fecha_limite, estado, etiquetas)
- POST /api/tasks — crear tarea
- GET /api/tasks/{id}
- PUT/PATCH /api/tasks/{id}
- DELETE /api/tasks/{id}
- POST /api/tasks/{id}/complete — marcar completada
- POST /api/notes/{id}/to-task — convertir nota en tarea (stub/logic)

Calendario (Events)
- GET /api/events
- POST /api/events
- GET /api/events/{id}
- PUT/PATCH /api/events/{id}
- DELETE /api/events/{id}

Recordatorios por correo
- Usar Jobs que se encolan cuando se crea/actualiza `fecha_recordatorio`. Worker procesará colas y enviará Mailables.

## Validación
- Usar form requests o validaciones en el controlador
- Restringir los recursos por usuario autenticado (políticas o comprobación user_id)

## Siguientes pasos
1. Crear migraciones y modelos Eloquent para Notes, Tasks y Events
2. Implementar controladores API y rutas
3. Jobs + Mailables para recordatorios
4. Vistas HTML mínimas y tests básicos
