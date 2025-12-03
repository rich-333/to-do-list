# Setup rápido — OrganizerAI (backend)

Requisitos:
- PHP 8.x
- Composer
- MySQL

Instalación básica:

1. Copiar .env y configurar DB + MAIL

   cp .env.example .env
   EDITAR los valores DB_* y MAIL_* en .env

2. Instalar dependencias y generar key

   composer install ; php artisan key:generate

3. Migraciones

   php artisan migrate

4. Configurar colas para recordatorios

   - En .env, setear QUEUE_CONNECTION=database (o redis si lo prefieres)
   - Asegúrate de ejecutar: php artisan queue:table y php artisan migrate (si usas database queue)
   - Iniciar worker: php artisan queue:work

5. Correo (en local)

   - Puedes usar mailtrap o smtp local — configurar en .env

Endpoint de prueba (desde navegador):

  - /organizer/notes
  - /organizer/tasks
  - /organizer/events

Comandos útiles:

  - Ejecutar tests: vendor/bin/phpunit (si el entorno está configurado)
