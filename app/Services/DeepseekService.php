<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DeepseekService
{
    public function generar($mensaje)
    {
        $response = Http::withToken(env('DEEPSEEK_API_KEY'))
            ->post('https://api.deepseek.com/chat/completions', [
                'model' => 'deepseek-chat',
                'messages' => [
                    ['role' => 'user', 'content' => $mensaje]
                ]
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? 'Error con IA';
    }
}
