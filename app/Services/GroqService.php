<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqService
{
    public function generar($mensaje)
    {
        $response = Http::withToken(env('GROQ_API_KEY'))
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    ['role' => 'user', 'content' => $mensaje]
                ]
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? 'Error con IA';
    }
}
