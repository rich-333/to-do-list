<?php

namespace App\Http\Controllers;

use App\Models\TaskList;
use App\Models\TaskListItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Return JSON lists for the authenticated user
    public function indexJson(Request $request)
    {
        $lists = TaskList::with('items')->where('user_id', Auth::id())->get();
        return response()->json($lists);
    }

    // Create a new list with optional items
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'items' => 'sometimes|array',
            'items.*.name' => 'required|string|max:255',
            'items.*.completed' => 'sometimes|boolean',
        ]);

        $list = TaskList::create([
            'title' => $data['title'],
            'user_id' => Auth::id(),
        ]);

        $items = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $idx => $it) {
                $items[] = $list->items()->create([
                    'name' => $it['name'],
                    'completed' => boolval($it['completed'] ?? false),
                    'order' => $idx,
                ]);
            }
        }

        $list->load('items');
        return response()->json($list, 201);
    }

    // Update list title
    public function update(Request $request, TaskList $taskList)
    {
        $this->authorizeOwnership($taskList);
        $data = $request->validate(['title' => 'required|string|max:255']);
        $taskList->update(['title' => $data['title']]);
        return response()->json($taskList);
    }

    // Add an item to a list
    public function addItem(Request $request, TaskList $taskList)
    {
        $this->authorizeOwnership($taskList);
        $data = $request->validate(['name' => 'required|string|max:255']);
        $order = $taskList->items()->count();
        $item = $taskList->items()->create(['name' => $data['name'], 'completed' => false, 'order' => $order]);
        return response()->json($item, 201);
    }

    // Update a specific item (name / completed)
    public function updateItem(Request $request, TaskList $taskList, TaskListItem $item)
    {
        $this->authorizeOwnership($taskList);
        if ($item->task_list_id !== $taskList->id) {
            return response()->json(['message' => 'Item no pertenece a la lista'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'completed' => 'sometimes|boolean',
        ]);

        $item->update($data);
        return response()->json($item);
    }

    public function destroy(TaskList $taskList)
    {
        $this->authorizeOwnership($taskList);
        $taskList->delete();
        return response()->json(['message' => 'Deleted']);
    }

    protected function authorizeOwnership(TaskList $taskList)
    {
        if ($taskList->user_id !== Auth::id()) {
            abort(403);
        }
    }

    // Sugerir items con IA (Groq, Deepseek o Gemini)
    public function suggestItems(Request $request)
    {
        \Log::info('[suggestItems] Request received', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'user_id' => Auth::id(),
            'has_title' => $request->has('title'),
            'input_keys' => array_keys($request->all()),
        ]);

        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'context' => 'sometimes|string|max:500',
                'provider' => 'sometimes|in:groq,deepseek,gemini', // elegir proveedor
            ]);

            \Log::info('[suggestItems] Validation passed', $data);

            $title = $data['title'];
            $context = $data['context'] ?? '';
            $provider = $data['provider'] ?? 'groq'; // por defecto Groq

            $prompt = "Genera 8-10 items útiles y específicos para una lista de compras/tareas llamada: '$title'";
            if ($context) {
                $prompt .= " (contexto: $context)";
            }
            $prompt .= "\nResponde SOLO en JSON válido con formato: {\"items\": [\"item1\", \"item2\", ...]}";

            $usedProvider = $provider;
            $usedProvider = $provider;

            // Intentar el proveedor solicitado; si falla, intentar alternativas en este orden: groq -> deepseek -> gemini
            $items = null;
            $attempted = [];

            try {
                $attempted[] = $provider;
                if ($provider === 'groq') {
                    $items = $this->getItemsFromGroq($prompt);
                } elseif ($provider === 'deepseek') {
                    $items = $this->getItemsFromDeepseek($prompt);
                } else {
                    $items = $this->getItemsFromGemini($prompt);
                }
            } catch (\Exception $e) {
                \Log::warning('[suggestItems] Proveedor inicial fallo (' . $provider . '): ' . $e->getMessage());

                // Lista de alternativas ordenadas
                $alternatives = ['groq', 'deepseek', 'gemini'];
                foreach ($alternatives as $alt) {
                    if (in_array($alt, $attempted)) {
                        continue;
                    }
                    try {
                        $attempted[] = $alt;
                        if ($alt === 'groq') {
                            $items = $this->getItemsFromGroq($prompt);
                        } elseif ($alt === 'deepseek') {
                            $items = $this->getItemsFromDeepseek($prompt);
                        } else {
                            $items = $this->getItemsFromGemini($prompt);
                        }
                        $usedProvider = $alt;
                        \Log::info('[suggestItems] Éxito con proveedor alternativo: ' . $alt);
                        break; // salir del loop si tuvo éxito
                    } catch (\Exception $e2) {
                        \Log::warning('[suggestItems] Alternativa ' . $alt . ' falló: ' . $e2->getMessage());
                        // seguir intentando otras alternativas
                    }
                }
            }

            // Si aún no hay items válidos, usar fallback local
            if (empty($items) || !is_array($items)) {
                \Log::warning('[suggestItems] Todos los proveedores fallaron o no devolvieron items. Usando fallback local.');
                $items = $this->generateLocalSuggestions($title, $context);
                $usedProvider = 'local';
            }
            // Retornar respuesta exitosa
            return response()->json(['items' => $items, 'provider' => $usedProvider]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[suggestItems] Validation error', $e->errors());
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('[suggestItems] Exception: ' . get_class($e), [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json(['error' => 'Error al generar sugerencias: ' . $e->getMessage()], 500);
        }

    }

    protected function getItemsFromGroq($prompt)
    {
        $apiKey = config('services.groq.key');
        if (!$apiKey) {
            throw new \Exception('GROQ_API_KEY no configurada en .env');
        }

        $client = new \GuzzleHttp\Client();

        // Obtener modelo principal y fallbacks desde config
        $primaryModel = config('services.groq.model', 'mixtral-8x7b-32768');
        $fallbacks = config('services.groq.fallbacks', []);
        $modelsToTry = array_values(array_filter(array_merge([$primaryModel], $fallbacks)));

        $lastExceptionMessage = '';

        foreach ($modelsToTry as $model) {
            try {
                \Log::info('[getItemsFromGroq] Intentando modelo Groq: ' . $model);

                $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
                    'headers' => [
                        'Authorization' => "Bearer $apiKey",
                        'Content-Type' => 'application/json',
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Eres un asistente que genera sugerencias de items para listas. Responde SIEMPRE en JSON válido.'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.7,
                        'max_tokens' => 500,
                    ],
                ]);

                $data = json_decode($response->getBody(), true);
                if (!isset($data['choices'][0]['message']['content'])) {
                    throw new \Exception('Respuesta inesperada de Groq: ' . json_encode($data));
                }

                $content = $data['choices'][0]['message']['content'];
                $parsed = json_decode($content, true);
                if (!is_array($parsed) || !isset($parsed['items'])) {
                    throw new \Exception('Groq no devolvió JSON válido con "items": ' . substr($content, 0, 200));
                }

                // Éxito
                return $parsed['items'];

            } catch (\GuzzleHttp\Exception\RequestException $e) {
                $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
                $lastExceptionMessage = 'Error Groq HTTP (modelo ' . $model . '): ' . $body;

                // Si el error indica que el modelo está desactivado, intentar siguiente fallback
                if (stripos($body, 'model_decommissioned') !== false || stripos($body, 'decommissioned') !== false) {
                    \Log::warning('[getItemsFromGroq] Modelo desactivado detectado: ' . $model . '. Intentando siguiente fallback si existe.');
                    continue; // probar siguiente modelo
                }

                // Otro tipo de error, no reintentar
                throw new \Exception($lastExceptionMessage);
            }
        }

        // Si llegamos aquí, no hubo modelos válidos
        throw new \Exception('Todos los modelos Groq intentados fallaron. Último error: ' . $lastExceptionMessage);
    }

    protected function getItemsFromDeepseek($prompt)
    {
        $apiKey = config('services.deepseek.key');
        if (!$apiKey) {
            throw new \Exception('DEEPSEEK_API_KEY no configurada en .env');
        }

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->post('https://api.deepseek.com/chat/completions', [
                'headers' => [
                    'Authorization' => "Bearer $apiKey",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'deepseek-chat',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Eres un asistente que genera sugerencias de items para listas. Responde SIEMPRE en JSON válido.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (!isset($data['choices'][0]['message']['content'])) {
                throw new \Exception('Respuesta inesperada de Deepseek: ' . json_encode($data));
            }

            $content = $data['choices'][0]['message']['content'];
            $parsed = json_decode($content, true);
            if (!is_array($parsed) || !isset($parsed['items'])) {
                throw new \Exception('Deepseek no devolvió JSON válido con "items": ' . substr($content, 0, 200));
            }

            return $parsed['items'];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('Error Deepseek HTTP: ' . $body);
        }
    }

    /**
     * Generador local de sugerencias si las APIs externas fallan.
     * Detecta el contexto (tarea/entrega vs lista de compra) y devuelve items relevantes.
     */
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
        // Retornar hasta 8 items
        return array_slice($items, 0, 8);
    }

    protected function getItemsFromGemini($prompt)
    {
        $apiKey = config('services.gemini.key');
        if (!$apiKey) {
            throw new \Exception('GEMINI_KEY no configurada en .env');
        }

        $client = new \GuzzleHttp\Client();

        // Use gemini-1.5-flash (current active model)
        $model = config('services.gemini.model', 'gemini-1.5-flash');

        try {
            $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 500,
                    ],
                ],
            ]);

            $data = json_decode($response->getBody(), true);
            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Respuesta inesperada de Gemini: ' . json_encode($data));
            }

            $content = $data['candidates'][0]['content']['parts'][0]['text'];
            $parsed = json_decode($content, true);
            if (!is_array($parsed) || !isset($parsed['items'])) {
                throw new \Exception('Gemini no devolvió JSON válido con "items": ' . substr($content, 0, 200));
            }

            return $parsed['items'];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $body = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            throw new \Exception('Error Gemini HTTP: ' . $body);
        }
    }
}
