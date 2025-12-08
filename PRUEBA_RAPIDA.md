# 🎯 INSTRUCCIONES RÁPIDAS PARA PROBAR LA CORRECCIÓN

## ✅ Cambios ya realizados:

1. ✅ **routes/web.php** - Ruta movida a `/ai/suggest-items`
2. ✅ **public/js/task-lists.js** - URL actualizada
3. ✅ **app/Http/Controllers/TaskListController.php** - Mejor logging y error handling
4. ✅ **storage/logs/** - Ahora hay más detalles de error

---

## 🚀 PASOS PARA PROBAR (elige UNO):

### OPCIÓN A: Desde el navegador (RECOMENDADO)

1. Abre tu navegador en `http://localhost:8000/organizer`
2. **Asegúrate que estés logueado** (si no, inicia sesión)
3. Haz clic en la pestaña **LISTAS**
4. Haz clic en el botón **"+ Nueva Lista"**
5. En el campo que aparece, escribe un nombre (ej: "Compras")
6. Haz clic en **"🤖 Sugerir con IA"**
7. Se abrirá un diálogo para seleccionar proveedor
8. Selecciona **"Groq"** (el predeterminado)
9. Espera unos segundos...
10. **Debería ver una lista de sugerencias** ✅

Si ves **"Error: Error al generar sugerencias (HTTP 422)"**, entonces:
- Abre **DevTools (F12)**
- Pestaña **Console**
- Copia y pega este código:

```javascript
console.log('Título ingresado:', document.querySelector('#new-list-input')?.value);
console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.content);
```

Y reporta qué ves.

---

### OPCIÓN B: Desde la consola del navegador

1. Abre `http://localhost:8000/organizer`
2. Presiona **F12** para abrir DevTools
3. Pestaña **Console**
4. Copia y pega esto:

```javascript
const csrf = document.querySelector('meta[name="csrf-token"]').content;

fetch('/ai/suggest-items', {
  method: 'POST',
  credentials: 'same-origin',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrf,
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    title: 'Compras',
    context: 'Para la semana',
    provider: 'groq'
  })
})
.then(r => {
  console.log('HTTP Status:', r.status);
  return r.json();
})
.then(d => console.log('Response:', d))
.catch(e => console.error('Error:', e));
```

5. Presiona **Enter**
6. Debería ver:
   - `HTTP Status: 200` (éxito) ✅
   - O `HTTP Status: 422` (validación falló) ❌
   - O `HTTP Status: 500` (error del servidor) ❌

---

### OPCIÓN C: Desde artisan tinker

1. Abre una terminal PowerShell
2. Ve a la carpeta del proyecto:
   ```powershell
   cd "c:\Users\MOLLERICONA\Downloads\PF\to-do-list"
   ```

3. Ejecuta:
   ```bash
   php artisan tinker
   ```

4. Copia y pega:
   ```php
   Auth::loginUsingId(1);
   $ctrl = new App\Http\Controllers\TaskListController();
   $req = new Illuminate\Http\Request();
   $req->request->add(['title' => 'Test', 'context' => '', 'provider' => 'groq']);
   $res = $ctrl->suggestItems($req);
   dd(json_decode($res->getContent(), true));
   ```

5. Debería ver un array con `'items' => [...]` ✅

---

## 📊 POSIBLES RESULTADOS:

### ✅ ÉXITO (debería ver esto):
```json
{
  "items": [
    "Leche",
    "Pan",
    "Queso",
    "Huevos",
    "Frutas",
    ...
  ],
  "provider": "groq"
}
```

### ❌ ERROR 422 (Validación):
```json
{
  "error": "Validation failed",
  "errors": {
    "title": ["The title field is required"]
  }
}
```
**Solución:** Asegúrate que el título no está vacío

### ❌ ERROR 500 (Servidor):
```json
{
  "error": "Error al generar sugerencias: API key not found"
}
```
**Solución:** Revisa que `.env` tiene las claves de API configuradas

---

## 🔍 SI SIGUE FALLANDO:

1. **Revisa los logs:**
   ```powershell
   Get-Content "c:\Users\MOLLERICONA\Downloads\PF\to-do-list\storage\logs\laravel.log" -Tail 20
   ```

2. **Busca líneas con `[suggestItems]`**

3. **Copia el error completo**

4. **Limpia cachés:**
   ```bash
   php artisan optimize:clear
   ```

5. **Intenta de nuevo**

---

## 📞 INFORMACIÓN ÚTIL

- **Endpoint:** `POST /ai/suggest-items`
- **Requiere:** autenticación (login)
- **Campos esperados:**
  - `title` (string, requerido, máx 255 caracteres)
  - `context` (string, opcional, máx 500 caracteres)
  - `provider` (string, opcional: `groq`, `deepseek`, o `gemini`)

---

**Listo para probar. Elige una opción arriba y cuéntame qué resulta.**
