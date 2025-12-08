# Guía de Pruebas - Funcionalidad de LISTAS

## Estado del Sistema

He verificado que toda la infraestructura está en su lugar:

✅ **Controlador**: `app/Http/Controllers/TaskListController.php` - Implementado correctamente
✅ **Modelos**: `TaskList.php` y `TaskListItem.php` - Configurados con relaciones
✅ **Migraciones**: Tablas `task_lists` y `task_list_items` - Definidas
✅ **Rutas**: Endpoints `/task-lists` - Definidos en `routes/web.php` (líneas 123-129)
✅ **JavaScript**: `public/js/task-lists.js` - Implementado con logging mejorado
✅ **Vista**: `resources/views/organizer/tabs/tasks.blade.php` - HTML correcto

## Pasos para Verificar la Funcionalidad

### 1. Verificar que la Aplicación está Corriendo

```bash
php artisan serve
```

Accede a: `http://localhost:8000`

### 2. Verificar que el Usuario está Autenticado

- Inicia sesión en la aplicación
- Abre el navegador (DevTools: F12 o Ctrl+Shift+I)
- Ve a la pestaña **Console** (Consola)
- Verifica que puedas ver los logs `[task-lists.js]`

### 3. Ejecutar las Migraciones (si no lo has hecho)

```bash
php artisan migrate
```

Esto creará las tablas:
- `task_lists` (para almacenar listas)
- `task_list_items` (para almacenar items dentro de cada lista)

### 4. Probar la Funcionalidad en el Navegador

1. Abre DevTools (F12)
2. Ve a **Console** (Consola)
3. Haz clic en la pestaña **LISTAS**
4. Verifica que puedas ver los logs en la consola:
   - `[task-lists.js] ✓ Módulo inicializado correctamente`
   - `[task-lists.js] Iniciando loadLists()`
   - `[task-lists.js] Respuesta recibida: 200 OK`
   - `[task-lists.js] Listas cargadas: [...]`

### 5. Crear una Nueva Lista

- Haz clic en el botón **+ Nueva lista**
- Deberá abrirse un modal para crear una nueva lista
- Ingresa un nombre (ej: "Mercado", "Compras")
- Opcionalmente agrega items
- Haz clic en "Guardar"

### 6. Verificar que los Datos se Almacenan

Si ves un error en la consola, copia el mensaje de error y comparte:
- El mensaje exacto del error
- El estado HTTP (401, 403, 500, etc.)
- Cualquier información adicional en la consola

## Posibles Errores y Soluciones

### Error: "No autenticado"
- **Problema**: El usuario no está autenticado
- **Solución**: Inicia sesión en la aplicación primero

### Error: "Error 500 en el servidor"
- **Problema**: Las migraciones no se ejecutaron
- **Solución**: Ejecuta `php artisan migrate`

### Error: "Respuesta inesperada del servidor"
- **Problema**: El controlador retorna HTML en lugar de JSON
- **Solución**: Verifica que estés accediendo a `/task-lists` (no a otra URL)

### Las listas no se muestran
- **Problema**: No hay datos en la base de datos
- **Solución**: Crea una nueva lista usando el botón "+ Nueva lista"

## Archivos Clave

| Archivo | Descripción |
|---------|-------------|
| `app/Http/Controllers/TaskListController.php` | Lógica CRUD para listas |
| `app/Models/TaskList.php` | Modelo de lista |
| `app/Models/TaskListItem.php` | Modelo de item en lista |
| `public/js/task-lists.js` | Funcionalidad frontend |
| `resources/views/organizer/tabs/tasks.blade.php` | HTML de la pestaña LISTAS |
| `routes/web.php` | Definición de endpoints (líneas 123-129) |

## Comandos Útiles

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Resetear la base de datos y ejecutar migraciones
php artisan migrate:fresh

# Crear datos de prueba (si existen seeders)
php artisan db:seed

# Verificar rutas disponibles
php artisan route:list | grep task-lists
```

---

Si después de estos pasos la funcionalidad aún no funciona, por favor:
1. Abre DevTools (F12)
2. Ve a la consola y copia todos los logs `[task-lists.js]`
3. Copia también cualquier error que veas en rojo
4. Comparte esta información para poder debugguear más específicamente
