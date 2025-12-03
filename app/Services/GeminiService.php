<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    public function generar($mensaje)
    {
        $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . env('GEMINI_KEY'), [
            'contents' => [
                ['parts' => [['text' => $mensaje]]]
            ]
        ]);

        return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'Error con IA';
    }
}
