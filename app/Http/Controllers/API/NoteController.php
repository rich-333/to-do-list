<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Note;
use App\Models\Task;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {
        $userId = $request->user()?->id ?? $request->query('usuario_id');
        $query = Note::query();
        if ($userId) $query->where('usuario_id', $userId);
        if ($request->filled('etiquetas')) {
            $tags = array_filter(explode(',', $request->query('etiquetas')));
            $query->whereJsonContains('etiquetas', $tags);
        }
        return new JsonResponse($query->latest()->paginate(25));
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'usuario_id' => 'sometimes|integer|exists:users,id',
            'titulo' => 'required|string|max:255',
            'contenido' => 'required|string',
            'etiquetas' => 'nullable|array',
            'color' => 'nullable|string|max:20',
        ]);

        $attrs['usuario_id'] = $request->user()?->id ?? ($attrs['usuario_id'] ?? null);

        if (empty($attrs['usuario_id'])) {
            return new JsonResponse(['message' => 'usuario_id requerido para peticiones sin auth'], 422);
        }

        $note = Note::create($attrs);
        return new JsonResponse($note, 201);
    }

    public function show(Note $note)
    {
        // permitir sólo al propietario si hay sesión
        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        return new JsonResponse($note);
    }

    public function update(Request $request, Note $note)
    {
        $attrs = $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'contenido' => 'sometimes|string',
            'etiquetas' => 'nullable|array',
            'color' => 'nullable|string|max:20',
        ]);

        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $note->update($attrs);
        return new JsonResponse($note);
    }

    public function destroy(Note $note)
    {
        if (Auth::check() && Auth::id() !== $note->usuario_id) {
            return new JsonResponse(['message' => 'Forbidden'], 403);
        }

        $note->delete();
        return new JsonResponse(null, 204);
    }

    // --- Optional AI-ish endpoints (stubs) ---
    public function summarize(Note $note)
    {
        // Simple stub that returns the first 120 chars
        $summary = substr(strip_tags($note->contenido), 0, 120);
        return new JsonResponse(['summary' => $summary]);
    }

    public function transform(Request $request)
    {
        $attrs = $request->validate([
            'text' => 'required|string',
            'operation' => 'sometimes|string|in:uppercase,lowercase,striphtml',
        ]);

        $text = $attrs['text'];
        if (($attrs['operation'] ?? null) === 'uppercase') $text = strtoupper($text);
        if (($attrs['operation'] ?? null) === 'lowercase') $text = strtolower($text);
        if (($attrs['operation'] ?? null) === 'striphtml') $text = strip_tags($text);

        return new JsonResponse(['result' => $text]);
    }

    public function toTask(Note $note, Request $request)
    {
        $titulo = $note->titulo;
        $task = Task::create([
            'usuario_id' => $note->usuario_id,
            'titulo' => $titulo,
            'descripcion' => $note->contenido,
            'prioridad' => $request->input('prioridad', 'media'),
            'estado' => 'pendiente',
            'etiquetas' => $note->etiquetas,
        ]);

        return new JsonResponse($task, 201);
    }

    /**
     * Convertir una nota a evento (calendar) y devolver el evento creado.
     */
    public function toEvent(Note $note, Request $request)
    {
        // Intentar extraer fecha/hora del contenido
        $content = strtolower(($note->titulo ?? '') . ' ' . ($note->contenido ?? ''));
        $dates = $this->extractDates($content);

        $inicio = $request->input('inicio');
        
        // Si no se pasa explícitamente, intentar parsear desde extractDates
        if (!$inicio && !empty($dates)) {
            // Buscar la primera fecha con formato parseble (iso8601, slash, dash, time)
            foreach ($dates as $date) {
                $dateStr = $date['date'] ?? null;
                $format = $date['format'] ?? null;
                
                if (!$dateStr) continue;
                
                try {
                    // Formatos ISO 8601 (YYYY-MM-DD)
                    if ($format === 'iso8601') {
                        $inicio = \Carbon\Carbon::parse($dateStr)->startOfDay();
                        break;
                    }
                    // Formato slash (DD/MM/YYYY)
                    elseif ($format === 'slash') {
                        $inicio = \Carbon\Carbon::createFromFormat('d/m/Y', $dateStr)->startOfDay();
                        break;
                    }
                    // Formato dash (DD-MM-YYYY)
                    elseif ($format === 'dash') {
                        $inicio = \Carbon\Carbon::createFromFormat('d-m-Y', $dateStr)->startOfDay();
                        break;
                    }
                    // Días de semana: calcular próxima ocurrencia
                    elseif ($format === 'text' && $date['type'] === 'day') {
                        $inicio = $this->getNextDayOfWeek($dateStr);
                        break;
                    }
                    // Palabras relativas: hoy, mañana, próximo, etc.
                    elseif ($format === 'relative') {
                        $inicio = $this->parseRelativeDate($dateStr);
                        if ($inicio) break;
                    }
                } catch (\Exception $e) {
                    \Log::warning('[NoteController@toEvent] No se pudo parsear fecha: ' . $dateStr . ' (' . $format . ')');
                    continue;
                }
            }
        }

        // Fallback seguro: si sigue sin inicio válido, usar ahora
        if (!$inicio) {
            $inicio = now();
        }

        try {
            $event = \App\Models\Event::create([
                'usuario_id' => $note->usuario_id,
                'titulo' => $note->titulo ?? 'Evento desde nota',
                'descripcion' => $note->contenido,
                'inicio' => $inicio,
            ]);

            // Programar recordatorios: 1 día antes y 15 minutos antes
            try {
                $inicioCarbon = \Carbon\Carbon::parse($event->inicio);
                $reminderDay = $inicioCarbon->copy()->subDay();
                $reminder15 = $inicioCarbon->copy()->subMinutes(15);

                // Guardar la fecha_recordatorio principal (1 día antes) si está en el futuro
                if ($reminderDay->greaterThan(now())) {
                    $event->fecha_recordatorio = $reminderDay;
                    $event->save();
                    \App\Jobs\SendEventReminderJob::dispatch($event)->delay($reminderDay);
                }

                // Programar recordatorio 15 minutos antes si está en el futuro
                if ($reminder15->greaterThan(now())) {
                    \App\Jobs\SendEventReminderJob::dispatch($event)->delay($reminder15);
                }
            } catch (\Exception $e) {
                \Log::warning('[NoteController@toEvent] No se pudieron programar recordatorios: ' . $e->getMessage());
            }

            return new JsonResponse($event, 201);
        } catch (\Exception $e) {
            \Log::error('[NoteController@toEvent] Error creando evento: ' . $e->getMessage());
            return new JsonResponse(['error' => 'No se pudo crear el evento: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Calcular la próxima ocurrencia de un día de la semana (p.ej. "domingo" → próximo domingo).
     * Si hoy ES ese día, devuelve hoy; si ya pasó esta semana, devuelve el próximo.
     */
    protected function getNextDayOfWeek($dayName)
    {
        $dayMap = [
            'lunes' => 1,
            'martes' => 2,
            'miércoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sábado' => 6,
            'domingo' => 0,
        ];
        
        $targetDay = $dayMap[strtolower(trim($dayName))] ?? null;
        if ($targetDay === null) {
            return now();
        }
        
        $today = now();
        $todayDayOfWeek = $today->dayOfWeek;
        
        // En Carbon: domingo=0, lunes=1, ..., sábado=6
        // Calcular días hasta el próximo targetDay
        $daysAhead = $targetDay - $todayDayOfWeek;
        
        if ($daysAhead <= 0) {
            // Si es hoy o ya pasó, ir al próximo (suma 7)
            $daysAhead += 7;
        }
        
        return $today->addDays($daysAhead)->startOfDay();
    }

    /**
     * Parsear palabras relativas como "hoy", "mañana", "próximo", etc.
     */
    protected function parseRelativeDate($relativeStr)
    {
        $rel = strtolower(trim($relativeStr));
        
        if ($rel === 'hoy') {
            return now()->startOfDay();
        } elseif ($rel === 'mañana') {
            return now()->addDay()->startOfDay();
        } elseif ($rel === 'pasado mañana') {
            return now()->addDays(2)->startOfDay();
        } elseif ($rel === 'próximo' || $rel === 'siguiente' || $rel === 'próxima semana') {
            return now()->addWeek()->startOfDay();
        } elseif ($rel === 'próximo mes') {
            return now()->addMonth()->startOfDay();
        }
        
        return null;
    }

    /**
     * Sugerir contenido para una nota mediante IA.
     * Reutiliza la lógica de `TaskListController::suggestItems` para evitar duplicar fallbacks.
     * Si no se pasa título, usará el título de la nota; context será el contenido de la nota.
     */
    public function suggest(Request $request, Note $note)
    {
        $attrs = $request->validate([
            'provider' => 'sometimes|string|in:groq,deepseek,gemini',
        ]);

        $title = $note->titulo ?? $request->input('title');
        $context = $note->contenido ?? $request->input('context', '');
        $provider = $attrs['provider'] ?? 'groq';

        // Construir una request simulada para reutilizar TaskListController::suggestItems
        $fake = Request::create('/', 'POST', [ 'title' => $title, 'context' => $context, 'provider' => $provider ]);

        // Ejecutar el controller existente para mantener la misma lógica de fallback y generación local
        try {
            // Crear instancia del controller y llamar el método en la instancia
            $controller = app()->make(\App\Http\Controllers\TaskListController::class);
            $result = $controller->suggestItems($fake);
            // $result es una instancia de JsonResponse
            $payload = json_decode($result->getContent(), true);
            return new JsonResponse($payload);
        } catch (\Exception $e) {
            \Log::error('[NoteController@suggest] Error al pedir sugerencias via TaskListController: ' . $e->getMessage());
            // Fallback local rápido si algo falla
            $local = $this->generateLocalSuggestions($title, $context);
            return new JsonResponse(['items' => $local, 'provider' => 'local']);
        }
    }

    // Helper local para generar sugerencias contextuales cuando los proveedores fallan
    protected function generateLocalSuggestions($title, $context = '')
    {
        // Detectar si el contenido parece ser tarea/entrega/recordatorio o lista de compra
        $combined = strtolower($title . ' ' . $context);
        
        // Palabras clave para tareas/entregas
        $taskKeywords = ['entregar', 'tarea', 'recordatorio', 'viernes', 'lunes', 'martes', 'miércoles', 'jueves', 'sábado', 'domingo', 'hacer', 'completar', 'estudiar', 'proyecto', 'trabajo', 'examen', 'preparar', 'revisar', 'matematicas', 'matemática'];
        
        // Palabras clave para compras
        $shoppingKeywords = ['compra', 'mercado', 'supermercado', 'lista', 'comprar', 'hacer falta', 'falta', 'necesito', 'alimentos', 'comida'];
        
        $isTask = false;
        $isShopping = false;
        foreach ($taskKeywords as $kw) {
            if (stripos($combined, $kw) !== false) {
                $isTask = true;
                break;
            }
        }
        foreach ($shoppingKeywords as $kw) {
            if (stripos($combined, $kw) !== false) {
                $isShopping = true;
                break;
            }
        }
        
        // Base de items según contexto detectado
        if ($isTask) {
            // Sugerencias para tareas/recordatorios
            $base = ['Revisar contenido', 'Preparar materiales', 'Completar antes de la fecha', 'Hacer una lista detallada', 'Consultar requisitos', 'Enviar confirmación', 'Guardar copia', 'Verificar antes de entregar'];
        } elseif ($isShopping) {
            // Sugerencias para lista de compra
            $base = ['Leche', 'Pan', 'Huevos', 'Queso', 'Frutas variadas', 'Verduras', 'Arroz', 'Pasta', 'Aceite', 'Azúcar', 'Sal', 'Café', 'Jabón', 'Detergente'];
        } else {
            // Fallback neutro: mezcla de ambas
            $base = ['Revisar contenido', 'Preparar materiales', 'Pan', 'Frutas variadas', 'Verduras', 'Arroz', 'Pasta', 'Aceite'];
        }
        
        // Extraer palabras útiles del título/contexto
        $extras = [];
        $commonWords = ['para', 'debo', 'debe', 'tengo', 'tener', 'hacer', 'ser', 'estar', 'que', 'del', 'los', 'las', 'una', 'unos', 'el', 'en', 'y', 'a', 'de'];
        foreach ([$title, $context] as $txt) {
            $words = preg_split('/[^\p{L}0-9]+/u', $txt);
            foreach ($words as $w) {
                $w = trim($w);
                // Excluir palabras muy comunes y las que ya están en la base
                if (mb_strlen($w) >= 3 && mb_strlen($w) <= 25 && !in_array(strtolower($w), $commonWords)) {
                    $extras[] = ucfirst(mb_strtolower($w));
                }
            }
        }
        
        // Mezclar y construir lista final única
        $items = array_values(array_unique(array_merge($extras, $base)));
        shuffle($items);
        return array_slice($items, 0, 8);
    }

    /**
     * Analiza el contenido de la nota y devuelve tipo, fechas, prioridad y acciones sugeridas.
     */
    public function analyze(Note $note)
    {
        $content = strtolower(($note->titulo ?? '') . ' ' . ($note->contenido ?? ''));

        $type = $this->detectContentType($content);
        $dates = $this->extractDates($content);

        // Si detectamos fechas, priorizamos tratar el contenido como evento
        if (!empty($dates)) {
            $type = 'event';
        }
        $priority = $this->detectPriority($content);
        $wordCount = str_word_count(strip_tags($note->contenido ?? ''));

        $suggestedActions = [];
        if ($type === 'event' && !empty($dates)) {
            $suggestedActions[] = [
                'action' => 'save_to_calendar',
                'label' => '📅 Guardar en calendario',
                'description' => 'Crear evento en calendario',
            ];
        }
        if (in_array($type, ['task', 'reminder'])) {
            $suggestedActions[] = [
                'action' => 'convert_to_task',
                'label' => '✅ Convertir a tarea',
                'description' => 'Crear una tarea desde esta nota',
            ];
        }
        if ($wordCount > 200) {
            $suggestedActions[] = [
                'action' => 'summarize',
                'label' => '📝 Resumir',
                'description' => 'Generar resumen de la nota',
            ];
        } elseif ($wordCount < 50) {
            $suggestedActions[] = [
                'action' => 'expand',
                'label' => '📖 Ampliar',
                'description' => 'Ampliar con más detalles',
            ];
        }

        return new JsonResponse([
            'type' => $type,
            'priority' => $priority,
            'detected_dates' => $dates,
            'word_count' => $wordCount,
            'suggested_actions' => $suggestedActions,
        ]);
    }

    /**
     * Expandir la nota usando IA (o fallback local).
     */
    public function expand(Request $request, Note $note)
    {
        $title = $note->titulo ?? 'Nota';
        $content = $note->contenido ?? '';
        $prompt = "Expande el siguiente contenido con más detalles y ejemplos:\n\nTitle: $title\nContent:\n$content\n";

        try {
            $result = $this->callAIProvider($prompt);
            $text = is_array($result) ? ($result['text'] ?? '') : $result;
            $provider = is_array($result) ? ($result['provider'] ?? 'local') : 'local';
            return new JsonResponse(['expanded' => $text, 'provider' => $provider]);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@expand] IA falló: '.$e->getMessage());
            $fallback = $content . "\n\n[Agregar más detalles, pasos y ejemplos aquí.]";
            return new JsonResponse(['expanded' => $fallback, 'provider' => 'local']);
        }
    }

    /**
     * Resumir el contenido de la nota usando IA (o fallback simple).
     */
    public function summarizeContent(Request $request, Note $note)
    {
        $title = $note->titulo ?? 'Nota';
        $content = $note->contenido ?? '';
        $prompt = "Resume en 2-3 oraciones las ideas principales del siguiente contenido:\n\nTitle: $title\nContent:\n$content\n";

        try {
            $result = $this->callAIProvider($prompt);
            $text = is_array($result) ? ($result['text'] ?? '') : $result;
            $provider = is_array($result) ? ($result['provider'] ?? 'local') : 'local';
            return new JsonResponse(['summary' => $text, 'provider' => $provider]);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@summarizeContent] IA falló: '.$e->getMessage());
            $lines = preg_split('/\r?\n/', trim($content));
            $fallback = implode(' ', array_slice($lines, 0, 3));
            return new JsonResponse(['summary' => $fallback, 'provider' => 'local']);
        }
    }

    /**
     * Llamar al proveedor IA reutilizando la lógica de TaskListController::suggestItems
     */
    protected function callAIProvider($prompt)
    {
        $fake = Request::create('/', 'POST', [ 'title' => 'Procesamiento', 'context' => $prompt, 'provider' => 'groq' ]);

        $controller = app()->make(\App\Http\Controllers\TaskListController::class);
        $result = $controller->suggestItems($fake);
        $payload = json_decode($result->getContent(), true);

        $provider = $payload['provider'] ?? 'local';
        if (is_array($payload['items'] ?? null)) {
            return ['text' => implode("\n", $payload['items']), 'provider' => $provider];
        }
        // Some providers may return a single 'result' or 'items' as string
        $text = $payload['items'] ?? ($payload['result'] ?? '');
        if (is_array($text)) $text = implode("\n", $text);
        return ['text' => $text, 'provider' => $provider];
    }

    protected function detectContentType($content)
    {
        $taskKeywords = ['tarea','debo','tengo que','hacer','completar','finalizar','recordar'];
        $eventKeywords = ['evento','reunion','reunión','cita','encuentro','fecha','mañana','hora'];
        $reminderKeywords = ['recordar','recordatorio','no olvides','recuerda','urgente'];

        foreach ($taskKeywords as $kw) if (stripos($content, $kw) !== false) return 'task';
        foreach ($eventKeywords as $kw) if (stripos($content, $kw) !== false) return 'event';
        foreach ($reminderKeywords as $kw) if (stripos($content, $kw) !== false) return 'reminder';
        return 'note';
    }

    protected function extractDates($content)
    {
        $dates = [];
        
        // 1. Detectar días de la semana (lunes, martes, etc.)
        $dayNames = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
        foreach ($dayNames as $d) {
            if (stripos($content, $d) !== false) {
                $dates[] = ['type'=>'day','date'=>ucfirst($d), 'format'=>'text'];
            }
        }
        
        // 2. Detectar meses (enero, febrero, etc.) + año opcional
        $monthNames = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        foreach ($monthNames as $m) {
            if (stripos($content, $m) !== false) {
                // Buscar patrón como "5 de diciembre" o "diciembre 5" o solo "diciembre"
                if (preg_match('/(\d{1,2})\s+de\s+' . $m . '|' . $m . '\s+(\d{1,2})/i', $content, $match)) {
                    $dates[] = ['type'=>'month','date'=>ucfirst($m), 'format'=>'text'];
                } else {
                    $dates[] = ['type'=>'month','date'=>ucfirst($m), 'format'=>'text'];
                }
            }
        }
        
        // 3. Detectar formato ISO 8601: YYYY-MM-DD
        if (preg_match_all('/\d{4}-\d{2}-\d{2}/', $content, $matches)) {
            foreach ($matches[0] as $m) {
                $dates[] = ['type'=>'date','date'=>$m, 'format'=>'iso8601'];
            }
        }
        
        // 4. Detectar formato DD/MM/YYYY o D/M/YYYY
        if (preg_match_all('/\d{1,2}\/\d{1,2}\/\d{2,4}/', $content, $matches)) {
            foreach ($matches[0] as $m) {
                $dates[] = ['type'=>'date','date'=>$m, 'format'=>'slash'];
            }
        }
        
        // 5. Detectar formato DD-MM-YYYY o D-M-YYYY
        if (preg_match_all('/\d{1,2}-\d{1,2}-\d{2,4}/', $content, $matches)) {
            foreach ($matches[0] as $m) {
                $dates[] = ['type'=>'date','date'=>$m, 'format'=>'dash'];
            }
        }
        
        // 6. Detectar horas (HH:MM, H:MM, incluyendo con AM/PM)
        if (preg_match_all('/\d{1,2}:\d{2}(?:\s*(?:AM|PM|am|pm))?/', $content, $matches)) {
            foreach ($matches[0] as $m) {
                $dates[] = ['type'=>'time','date'=>trim($m), 'format'=>'time'];
            }
        }
        
        // 7. Detectar palabras clave temporales
        $tempKeywords = ['hoy','mañana','pasado mañana','próximo','siguiente','este','próxima semana','próximo mes'];
        foreach ($tempKeywords as $kw) {
            if (stripos($content, $kw) !== false) {
                $dates[] = ['type'=>'relative','date'=>$kw, 'format'=>'relative'];
            }
        }
        
        return array_values(array_unique($dates, SORT_REGULAR));
    }

    protected function detectPriority($content)
    {
        $high = ['urgente','importante','hoy','ahora','inmediato'];
        $low = ['cuando puedas','si puedes','quizás','eventualmente'];
        foreach ($high as $kw) if (stripos($content, $kw) !== false) return 'high';
        foreach ($low as $kw) if (stripos($content, $kw) !== false) return 'low';
        return 'medium';
    }

    /**
     * Analyze raw content (no note saved yet) and return same structure as analyze(Note $note).
     */
    public function analyzeRaw(Request $request)
    {
        $attrs = $request->validate([
            'title' => 'sometimes|string',
            'content' => 'required|string',
        ]);

        $content = strtolower(($attrs['title'] ?? '') . ' ' . ($attrs['content'] ?? ''));
        $type = $this->detectContentType($content);
        $dates = $this->extractDates($content);
        if (!empty($dates)) $type = 'event';
        $priority = $this->detectPriority($content);
        $wordCount = str_word_count($attrs['content']);

        $suggestedActions = [];
        if ($type === 'event' && !empty($dates)) {
            $suggestedActions[] = ['action' => 'save_to_calendar', 'label' => '📅 Guardar en calendario', 'description' => 'Crear evento en calendario'];
        }
        if (in_array($type, ['task', 'reminder'])) {
            $suggestedActions[] = ['action' => 'convert_to_task', 'label' => '✅ Convertir a tarea', 'description' => 'Crear una tarea desde esta nota'];
        }
        if ($wordCount > 200) $suggestedActions[] = ['action'=>'summarize','label'=>'📝 Resumir','description'=>'Generar resumen'];
        elseif ($wordCount < 50) $suggestedActions[] = ['action'=>'expand','label'=>'📖 Ampliar','description'=>'Ampliar con más detalles'];

        return new JsonResponse(['type'=>$type,'priority'=>$priority,'detected_dates'=>$dates,'word_count'=>$wordCount,'suggested_actions'=>$suggestedActions]);
    }

    public function expandRaw(Request $request)
    {
        $attrs = $request->validate(['title'=>'sometimes|string','content'=>'required|string']);
        $prompt = "Expande y desarrolla el siguiente contenido con más detalles:\n\nTitulo: " . ($attrs['title'] ?? '') . "\nContenido:\n" . $attrs['content'];
        try {
            $result = $this->callAIProvider($prompt);
            $text = is_array($result) ? ($result['text'] ?? '') : $result;
            $provider = is_array($result) ? ($result['provider'] ?? 'local') : 'local';
            return new JsonResponse(['expanded'=>$text, 'provider' => $provider]);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@expandRaw] ' . $e->getMessage());
            return new JsonResponse(['expanded' => $attrs['content'] . "\n\n[Ampliar manualmente...]", 'provider' => 'local']);
        }
    }

    public function summarizeRaw(Request $request)
    {
        $attrs = $request->validate(['title'=>'sometimes|string','content'=>'required|string']);
        $prompt = "Resume en 2-3 oraciones las ideas principales del siguiente contenido:\n\nTitulo: " . ($attrs['title'] ?? '') . "\nContenido:\n" . $attrs['content'];
        try {
            $result = $this->callAIProvider($prompt);
            $text = is_array($result) ? ($result['text'] ?? '') : $result;
            $provider = is_array($result) ? ($result['provider'] ?? 'local') : 'local';
            return new JsonResponse(['summary'=>$text, 'provider' => $provider]);
        } catch (\Exception $e) {
            \Log::warning('[NoteController@summarizeRaw] ' . $e->getMessage());
            $lines = preg_split('/\r?\n/', trim($attrs['content']));
            return new JsonResponse(['summary' => implode(' ', array_slice($lines,0,3)), 'provider' => 'local']);
        }
    }

    /**
     * Suggest raw content items using same fallback as suggest()
     */
    public function suggestRaw(Request $request)
    {
        $attrs = $request->validate(['provider'=>'sometimes|string|in:groq,deepseek,gemini','title'=>'sometimes|string','content'=>'required|string']);
        $title = $attrs['title'] ?? '';
        $context = $attrs['content'];

        $fake = Request::create('/', 'POST', ['title'=>$title,'context'=>$context,'provider'=>$attrs['provider'] ?? 'groq']);

        try {
            $controller = app()->make(\App\Http\Controllers\TaskListController::class);
            $result = $controller->suggestItems($fake);
            $payload = json_decode($result->getContent(), true);
            return new JsonResponse($payload);
        } catch (\Exception $e) {
            \Log::error('[NoteController@suggestRaw] ' . $e->getMessage());
            $local = $this->generateLocalSuggestions($title, $context);
            return new JsonResponse(['items'=>$local,'provider'=>'local']);
        }
    }
}
