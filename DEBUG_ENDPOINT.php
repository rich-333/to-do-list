<?php
/**
 * Endpoint de diagnóstico para /ai/suggest-items
 * 
 * Este archivo ayuda a identificar exactamente por qué falla la solicitud.
 * Agrégalo temporalmente a routes/web.php para debugging:
 * Route::post('/debug/ai-suggest', [TaskListController::class, 'debugSuggestItems']);
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskListController extends Controller
{
    /**
     * Método de debugging para suggestItems
     * Retorna información detallada sobre qué está fallando
     */
    public function debugSuggestItems(Request $request)
    {
        $debug = [
            'timestamp' => now()->toIso8601String(),
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'user_authenticated' => auth()->check(),
            'user_id' => auth()->id(),
            'headers' => [
                'content-type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'csrf-token-present' => $request->header('X-CSRF-TOKEN') ? 'yes' : 'no',
            ],
            'input_raw' => $request->getContent(),
            'input_parsed' => $request->all(),
            'errors' => [],
        ];

        // Intentar validar
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'context' => 'sometimes|string|max:500',
                'provider' => 'sometimes|in:groq,deepseek,gemini',
            ]);
            $debug['validation'] = 'PASSED';
            $debug['validated_data'] = $validated;
        } catch (\Illuminate\Validation\ValidationException $e) {
            $debug['validation'] = 'FAILED';
            $debug['validation_errors'] = $e->errors();
        }

        // Verificar APIs
        $debug['api_keys'] = [
            'groq' => config('services.groq.key') ? 'configured' : 'MISSING',
            'deepseek' => config('services.deepseek.key') ? 'configured' : 'MISSING',
            'gemini' => config('services.gemini.key') ? 'configured' : 'MISSING',
        ];

        return response()->json($debug, 200);
    }
}
