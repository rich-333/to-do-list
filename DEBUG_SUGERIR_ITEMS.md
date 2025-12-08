# Debugging: Error al generar sugerencias

## Pasos para diagnosticar el problema

### 1. Limpiar caches (YA REALIZADO)
```bash
php artisan optimize:clear
composer dump-autoload
```

### 2. Revisar en DevTools (F12) del navegador

**Pasos:**
1. Abre el navegador y ve a http://localhost:8000
2. Inicia sesión
3. Abre **DevTools** (F12)
4. Ve a la pestaña **Console** (Consola)
5. Ve a pestaña **LISTAS** → **+ Nueva lista**
6. Ingresa un título (ej: "Prueba")
7. Haz clic en **🤖 Sugerir con IA**
8. Mira la consola para ver los logs:
   ```
   [task-lists.js] Solicitando sugerencias: título=Prueba, proveedor=groq
   [task-lists.js] Respuesta HTTP: status=..., ok=...
   ```

**Si ves error HTTP 500:**
- Ve a la pestaña **Network**
- Busca la petición a `/task-lists/ai/suggest-items`
- Haz clic en ella
- Abre la pestaña **Response**
- Copia el JSON con el error

### 3. Revisar logs del servidor

```bash
# En PowerShell
Get-Content storage/logs/laravel.log -Tail 50
```

Busca líneas que contengan:
- `[suggestItems]`
- `Error`
- `exception`

### 4. Verificar conexión a APIs (IMPORTANTE)

Abre una terminal y ejecuta:

```bash
php artisan tinker
```

Luego copia y pega el contenido de `test-apis-tinker.php`:

```php
$client = new \GuzzleHttp\Client();
echo "Groq Key: " . (strlen(config('services.groq.key')) > 5 ? "OK" : "ERROR") . "\n";
echo "Deepseek Key: " . (strlen(config('services.deepseek.key')) > 5 ? "OK" : "ERROR") . "\n";
echo "Gemini Key: " . (strlen(config('services.gemini.key')) > 5 ? "OK" : "ERROR") . "\n";

// Test Groq
try {
    $res = $client->post('https://api.groq.com/openai/v1/chat/completions', [
        'headers' => ['Authorization' => 'Bearer ' . config('services.groq.key'), 'Content-Type' => 'application/json'],
        'json' => ['model' => 'mixtral-8x7b-32768', 'messages' => [['role' => 'user', 'content' => 'Test']], 'max_tokens' => 50],
        'timeout' => 10,
    ]);
    echo "✓ Groq funciona (HTTP " . $res->getStatusCode() . ")\n";
} catch (\Exception $e) {
    echo "✗ Groq error: " . substr($e->getMessage(), 0, 100) . "\n";
}
```

Si ves "✗ Groq error", significa:
- Las claves API son inválidas
- No hay conexión a internet
- La API está caída

### 5. Verificar que las rutas están registradas

```bash
php artisan route:list | grep "suggest-items"
```

Deberías ver:
```
POST   /task-lists/ai/suggest-items
```

### 6. Verificar el controlador

```bash
php artisan tinker

# Verificar que el método existe
$c = new \App\Http\Controllers\TaskListController();
method_exists($c, 'suggestItems') ? echo "✓ Método existe" : echo "✗ Método NO existe";
```

---

## Errores comunes y soluciones

### Error: "Call to undefined method App\Http\Controllers\TaskListController::middleware()"
**Solución:** Las caches aún contienen la clase vieja.
```bash
php artisan optimize:clear
composer dump-autoload
```

### Error: "GROQ_API_KEY no configurada en .env"
**Solución:** Las claves API no están en `.env`
- Verifica que en `.env` tienes:
  ```
  GROQ_API_KEY=gsk_...
  DEEPSEEK_API_KEY=sk_...
  GEMINI_KEY=AIza...
  ```
- Si las cambiaste, reinicia el servidor: `php artisan serve`

### Error: "GROQ_API_KEY inválida" o "Unauthorized"
**Solución:** La clave API expiró o es incorrecta.
- Genera nuevas claves en:
  - Groq: https://console.groq.com/keys
  - Deepseek: https://platform.deepseek.com/api
  - Gemini: https://aistudio.google.com/app/apikey

### Error: "Connection timeout" o "Unable to connect"
**Solución:** Sin conexión a internet o la API está caída.
- Verifica que puedas navegar a: https://api.groq.com/
- Intenta con otra API (Deepseek o Gemini)

### Error: "Groq no devolvió JSON válido"
**Solución:** La IA devolvió algo que no es JSON.
- Esto puede pasar si pides poco context
- Intenta con un título más específico

---

## Qué compartir si sigue sin funcionar

1. **Screenshot de la consola (F12)** mostrando el error
2. **Último log en storage/logs/laravel.log** (las últimas 20 líneas)
3. **Output del test de APIs** (de tinker)
4. **Respuesta de la petición** en la pestaña Network (JSON con el error)

---

## Checklist de verificación

- [ ] Limpiaste caches: `php artisan optimize:clear`
- [ ] Regeneraste autoloader: `composer dump-autoload`
- [ ] Verificaste las claves en `.env`
- [ ] Abriste DevTools y viste los logs
- [ ] Probaste la conexión a las APIs (tinker)
- [ ] El servidor está corriendo: `php artisan serve`
- [ ] Estás autenticado en la app
- [ ] Ingresaste un título en la lista

Si todo esto funciona y sigue sin ir, compartelo con los detalles de arriba.
