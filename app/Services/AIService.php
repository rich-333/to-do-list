<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AIService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('OPENAI_API_KEY');
    }

    public function generarTexto($mensaje)
    {
        $response = Http::withToken($this->apiKey)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => $mensaje]
                ]
            ]);

        return $response->json()['choices'][0]['message']['content'] ?? 'Error con la IA';
    }
}
