# Implementación: Notas Inteligentes con Acciones Contextuales

## Plan Completo

Voy a implementar un sistema de **análisis inteligente de notas** que detecta automáticamente:
- ✅ Tipo de contenido (tarea, evento, recordatorio, idea, etc.)
- ✅ Fechas mencionadas (para eventos/calendar)
- ✅ Prioridad (alta, media, baja)
- ✅ Acciones sugeridas (resumir, ampliar, convertir a tarea, guardar en calendario)

---

## 1. Nuevas Rutas API (agregar en `routes/api.php`)

```php
// Dentro de Route::prefix('v1')->group(function () {

    // Rutas existentes
    Route::apiResource('notes', NoteController::class);
    Route::post('notes/{note}/ai/suggest', [NoteController::class, 'suggest']);
    
    // NUEVAS RUTAS
    Route::post('notes/{note}/ai/analyze', [NoteController::class, 'analyze']);
    Route::post('notes/{note}/ai/expand', [NoteController::class, 'expand']);
    Route::post('notes/{note}/ai/summarize-content', [NoteController::class, 'summarizeContent']);
    
    // Rutas existentes
    Route::post('notes/{note}/summarize', [NoteController::class, 'summarize']);
    Route::post('notes/transform', [NoteController::class, 'transform']);
    Route::post('notes/{note}/to-task', [NoteController::class, 'toTask']);
    
    // ... resto de rutas
```

---

## 2. Métodos a Agregar en `app/Http/Controllers/API/NoteController.php`

Agregar estos métodos al final de la clase (antes del cierre `}`):

```php

    /**
     * Analizar el contenido de una nota e identificar tipo, fechas, acciones sugeridas.
     * POST /api/v1/notes/{note}/ai/analyze
     */
    public function analyze(Note $note)
    {
        $content = strtolower($note->contenido . ' ' . ($note->titulo ?? ''));
        
        // Detectar tipo de contenido
        $type = $this->detectContentType($content);
        
        // Detectar fechas mencionadas
        $dates = $this->extractDates($content);
        
        // Detectar prioridad
        $priority = $this->detectPriority($content);
        
        // Sugerir acciones disponibles
        $suggestedActions = [];
        
        if ($type === 'event' && !empty($dates)) {
            $suggestedActions[] = [
                'action' => 'save_to_calendar',
                'label' => '📅 Guardar en calendario',
                'description' => 'Crear evento para ' . reset($dates)['date'],
                'icon' => 'calendar'
            ];
        }
        
        if (in_array($type, ['task', 'reminder'])) {
            $suggestedActions[] = [
                'action' => 'convert_to_task',
                'label' => '✅ Convertir a tarea',
                'description' => 'Crear una tarea desde esta nota',
                'icon' => 'check-circle'
            ];
        }
        
        // Contar palabras para sugerir expandir o resumir
        $wordCount = str_word_count($note->contenido);
        if ($wordCount > 200) {
            $suggestedActions[] = [
                'action' => 'summarize',
                'label' => '📝 Resumir',
                'description' => 'Generar resumen (actualmente ' . $wordCount . ' palabras)',
                'icon' => 'compress'
            ];
        } elseif ($wordCount < 50) {
            $suggestedActions[] = [
                'action' => 'expand',
                'label' => '📖 Ampliar',
                'description' => 'Generar más detalles (actualmente ' . $wordCount . ' palabras)',
                'icon' => 'expand'
            ];
        }
        
        return new JsonResponse([
            'type' => $type,
            'priority' => $priority,
            'detected_dates' => $dates,
            'word_count' => $wordCount,
            'suggested_actions' => $suggestedActions
        ]);
    }

    /**
     * Ampliar el contenido de una nota usando IA.
     * POST /api/v1/notes/{note}/ai/expand
     */
    public function expand(Request $request, Note $note)
    {
        $title = $note->titulo ?? 'Contenido';
        $content = $note->contenido;
        
        $prompt = "Expande y desarrolla el siguiente contenido con más detalles, ejemplos y contexto. Mantén el tono original:\n\nTítulo: $title\nContenido:\n$content\n\nResponde SOLO con el contenido expandido, sin explicaciones adicionales.";
        
        try {
            $expanded = $this->callAIProvider($prompt);
            return new JsonResponse(['expanded' => $expanded, 'provider' => 'ai']);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@expand] Error con IA: ' . $e->getMessage());
            // Fallback: agregar sugerencias genéricas
            $fallback = $content . "\n\n[Añade aquí más detalles, ejemplos o contexto relevante]";
            return new JsonResponse(['expanded' => $fallback, 'provider' => 'local']);
        }
    }

    /**
     * Resumir el contenido de una nota usando IA.
     * POST /api/v1/notes/{note}/ai/summarize-content
     */
    public function summarizeContent(Request $request, Note $note)
    {
        $title = $note->titulo ?? 'Contenido';
        $content = $note->contenido;
        
        $prompt = "Resume el siguiente contenido en 2-3 oraciones clave, manteniendo la información más importante:\n\nTítulo: $title\nContenido:\n$content\n\nResponde SOLO con el resumen, sin explicaciones.";
        
        try {
            $summary = $this->callAIProvider($prompt);
            return new JsonResponse(['summary' => $summary, 'provider' => 'ai']);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@summarizeContent] Error con IA: ' . $e->getMessage());
            // Fallback: primeras líneas
            $lines = explode("\n", $content);
            $fallback = implode(" ", array_slice($lines, 0, 3));
            return new JsonResponse(['summary' => $fallback, 'provider' => 'local']);
        }
    }

    /**
     * Llamar un proveedor IA para procesamiento de texto.
     * Reutiliza la lógica de fallback existente.
     */
    protected function callAIProvider($prompt)
    {
        $fake = new \Illuminate\Http\Request();
        $fake->setMethod('POST');
        $fake->request->add([ 'title' => 'Procesamiento', 'context' => $prompt, 'provider' => 'groq' ]);
        
        try {
            $controller = app()->make(\App\Http\Controllers\TaskListController::class);
            $result = $controller->suggestItems($fake);
            $payload = json_decode($result->getContent(), true);
            
            // Extraer texto de respuesta (si es un array de items, unir con espacios)
            if (is_array($payload['items'] ?? null)) {
                return implode(", ", $payload['items']);
            }
            return $payload['items'] ?? "No se pudo procesar.";
        } catch (\Exception $e) {
            throw new \Exception("Error al llamar proveedor: " . $e->getMessage());
        }
    }

    /**
     * Detectar el tipo de contenido de la nota.
     */
    protected function detectContentType($content)
    {
        $taskKeywords = ['tarea', 'debo', 'tengo que', 'hacer', 'completar', 'finalizar'];
        $eventKeywords = ['evento', 'reunion', 'encuentro', 'cita', 'viernes', 'lunes', 'mañana', 'hora', 'tiempo'];
        $reminderKeywords = ['recordar', 'recordatorio', 'no olvides', 'recuerda', 'importante', 'urgente'];
        $ideaKeywords = ['idea', 'pensamiento', 'concepto', 'noción', 'ocurrencia'];
        
        foreach ($taskKeywords as $kw) {
            if (stripos($content, $kw) !== false) return 'task';
        }
        foreach ($eventKeywords as $kw) {
            if (stripos($content, $kw) !== false) return 'event';
        }
        foreach ($reminderKeywords as $kw) {
            if (stripos($content, $kw) !== false) return 'reminder';
        }
        foreach ($ideaKeywords as $kw) {
            if (stripos($content, $kw) !== false) return 'idea';
        }
        
        return 'note';
    }

    /**
     * Extraer fechas mencionadas en el contenido.
     */
    protected function extractDates($content)
    {
        $dates = [];
        $dayNames = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
        $monthNames = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        
        foreach ($dayNames as $day) {
            if (stripos($content, $day) !== false) {
                $dates[] = ['type' => 'day', 'date' => ucfirst($day)];
            }
        }
        
        foreach ($monthNames as $month) {
            if (stripos($content, $month) !== false) {
                $dates[] = ['type' => 'month', 'date' => ucfirst($month)];
            }
        }
        
        // Buscar patrones como "2025-12-05" o "05/12/2025"
        if (preg_match('/\d{4}-\d{2}-\d{2}/', $content, $matches)) {
            $dates[] = ['type' => 'date', 'date' => $matches[0]];
        }
        
        return $dates;
    }

    /**
     * Detectar prioridad basada en palabras clave.
     */
    protected function detectPriority($content)
    {
        $highPriority = ['urgente', 'importante', 'crítico', 'hoy', 'ahora', 'inmediato', 'pronto'];
        $lowPriority = ['cuando puedas', 'si puedes', 'quizás', 'eventualmente'];
        
        foreach ($highPriority as $kw) {
            if (stripos($content, $kw) !== false) return 'high';
        }
        foreach ($lowPriority as $kw) {
            if (stripos($content, $kw) !== false) return 'low';
        }
        
        return 'medium';
    }
```

---

## 3. Actualizar Vista `resources/views/organizer/note.blade.php`

Reemplazar la sección del botón "Sugerir con IA" por esta versión mejorada:

```blade
<p style="margin-top:0.75rem">
  <button type="submit" id="save-btn">Guardar</button> 
  <a href="/">Volver</a>
</p>

<!-- Sección de Acciones IA -->
<div id="ai-actions" style="margin-top:1.5rem; padding:1rem; background:#f0f4f8; border-radius:8px; display:none;">
  <h3 style="margin-top:0;">🤖 Acciones Sugeridas</h3>
  <div id="actions-list" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:0.75rem;"></div>
  <div id="ai-status" style="color:#666; font-size:0.9em; margin-top:0.5rem;"></div>
</div>

<p style="margin-top:0.75rem">
  <button id="analyze-btn" type="button" style="background:#9c27b0; color:white;">🔍 Analizar con IA</button>
  <button id="suggest-ia" type="button" style="background:#ff9800;">🤖 Sugerir Items</button>
  <span id="ia-note-msg" style="margin-left:12px;color:#666"></span>
</p>

<script>
(function(){
  const analyzeBtn = document.getElementById('analyze-btn');
  const actionsContainer = document.getElementById('ai-actions');
  const actionsList = document.getElementById('actions-list');
  const aiStatus = document.getElementById('ai-status');
  const csrf = document.querySelector('meta[name="csrf-token"]').content || '';
  
  analyzeBtn?.addEventListener('click', async (e) => {
    e.preventDefault();
    aiStatus.textContent = '🔄 Analizando...';
    actionsList.innerHTML = '';
    
    try {
      const res = await fetch('/api/v1/notes/{{ $note->id }}/ai/analyze', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      });
      
      const data = await res.json();
      if (res.ok) {
        actionsContainer.style.display = 'block';
        aiStatus.textContent = `📊 Detectado: ${data.type} (Prioridad: ${data.priority}) | ${data.word_count} palabras`;
        
        // Mostrar botones de acciones sugeridas
        if (data.suggested_actions && data.suggested_actions.length > 0) {
          data.suggested_actions.forEach(action => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = action.label;
            btn.title = action.description;
            btn.style.cssText = 'padding:0.5rem 1rem; background:#673ab7; color:white; border:none; border-radius:6px; cursor:pointer;';
            btn.addEventListener('click', () => executeAction(action.action));
            actionsList.appendChild(btn);
          });
        }
      } else {
        aiStatus.textContent = '❌ Error: ' + (data.error || 'Desconocido');
      }
    } catch (err) {
      aiStatus.textContent = '❌ ' + (err.message || err);
    }
  });
  
  async function executeAction(action) {
    const ta = document.getElementById('contenido');
    aiStatus.textContent = '⏳ Ejecutando ' + action + '...';
    
    try {
      let url = '';
      let method = 'POST';
      let resultKey = '';
      
      if (action === 'summarize') {
        url = '/api/v1/notes/{{ $note->id }}/ai/summarize-content';
        resultKey = 'summary';
      } else if (action === 'expand') {
        url = '/api/v1/notes/{{ $note->id }}/ai/expand';
        resultKey = 'expanded';
      } else if (action === 'convert_to_task') {
        // Redirigir al endpoint toTask existente
        url = '/api/v1/notes/{{ $note->id }}/to-task';
        resultKey = 'titulo';
      } else if (action === 'save_to_calendar') {
        aiStatus.textContent = '📅 Funcionalidad de calendario — próximamente disponible';
        return;
      } else {
        aiStatus.textContent = 'Acción no reconocida: ' + action;
        return;
      }
      
      const res = await fetch(url, {
        method: method,
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
      });
      
      const data = await res.json();
      if (res.ok) {
        if (action === 'summarize') {
          ta.value = data.summary + '\n\n---\n\n' + ta.value;
          aiStatus.textContent = '✅ Resumen añadido al inicio';
        } else if (action === 'expand') {
          ta.value = data.expanded;
          aiStatus.textContent = '✅ Contenido ampliado';
        } else if (action === 'convert_to_task') {
          aiStatus.textContent = '✅ Tarea creada: ' + data.titulo;
          setTimeout(() => location.href = '/', 2000);
        }
      } else {
        aiStatus.textContent = '❌ Error: ' + (data.error || JSON.stringify(data));
      }
    } catch (err) {
      aiStatus.textContent = '❌ ' + (err.message || err);
    }
  }
})();
</script>
```

---

## 4. Pasos para Implementar

1. **Actualiza `routes/api.php`** con las nuevas rutas (ver sección 1).

2. **Copia y pega los nuevos métodos en `app/Http/Controllers/API/NoteController.php`** (sección 2) — agrégalos antes del cierre final `}` de la clase.

3. **Reemplaza la sección IA en `resources/views/organizer/note.blade.php`** con el código de la sección 3.

4. **Limpia cachés:**
   ```powershell
   php artisan config:clear
   php artisan optimize:clear
   ```

5. **Prueba:**
   - Abre una nota
   - Haz clic en "🔍 Analizar con IA"
   - Observa los botones de acciones sugeridas

---

## 5. Acciones Disponibles

| Acción | Cuándo aparece | Qué hace |
|--------|---|---|
| 📅 Guardar en calendario | Si detecta evento + fecha | Permitirá crear evento en calendario (próxima fase) |
| ✅ Convertir a tarea | Si detecta tarea/recordatorio | Crea una tarea desde la nota |
| 📝 Resumir | Si la nota > 200 palabras | Genera resumen usando IA |
| 📖 Ampliar | Si la nota < 50 palabras | Genera contenido expandido |

---

## 6. Próximas Mejoras

- [ ] Integración con calendario (crear eventos)
- [ ] Detectar contactos mencionados
- [ ] Sugerir archivos relacionados
- [ ] Clasificación automática por categorías/colores
- [ ] Búsqueda semántica entre notas

