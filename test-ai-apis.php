#!/usr/bin/env php
<?php
/**
 * Script de prueba para las APIs de IA
 * Uso: php artisan tinker < test-ai-apis.php
 * O directamente: php test-ai-apis.php
 */

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "=== Prueba de Configuración de APIs ===\n\n";

// Verificar claves configuradas
echo "1. GROQ_API_KEY configurada: " . (strlen(config('services.groq.key')) > 0 ? '✓ SÍ' : '✗ NO') . "\n";
echo "2. DEEPSEEK_API_KEY configurada: " . (strlen(config('services.deepseek.key')) > 0 ? '✓ SÍ' : '✗ NO') . "\n";
echo "3. GEMINI_KEY configurada: " . (strlen(config('services.gemini.key')) > 0 ? '✓ SÍ' : '✗ NO') . "\n\n";

// Probar Groq
echo "=== Prueba de Groq ===\n";
try {
    $client = new \GuzzleHttp\Client();
    $apiKey = config('services.groq.key');
    
    $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
        'headers' => [
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'mixtral-8x7b-32768',
            'messages' => [
                ['role' => 'system', 'content' => 'Responde en JSON: {"test": "ok"}'],
                ['role' => 'user', 'content' => 'Test'],
            ],
            'max_tokens' => 100,
        ],
        'timeout' => 10,
    ]);

    $data = json_decode($response->getBody(), true);
    echo "✓ Groq respondió correctamente (status: " . $response->getStatusCode() . ")\n";
    echo "  Respuesta: " . substr($data['choices'][0]['message']['content'], 0, 100) . "\n";
} catch (\Exception $e) {
    echo "✗ Error en Groq: " . $e->getMessage() . "\n";
}

echo "\n";

// Probar Deepseek
echo "=== Prueba de Deepseek ===\n";
try {
    $client = new \GuzzleHttp\Client();
    $apiKey = config('services.deepseek.key');
    
    $response = $client->post('https://api.deepseek.com/chat/completions', [
        'headers' => [
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => 'Responde en JSON: {"test": "ok"}'],
                ['role' => 'user', 'content' => 'Test'],
            ],
            'max_tokens' => 100,
        ],
        'timeout' => 10,
    ]);

    $data = json_decode($response->getBody(), true);
    echo "✓ Deepseek respondió correctamente (status: " . $response->getStatusCode() . ")\n";
    echo "  Respuesta: " . substr($data['choices'][0]['message']['content'], 0, 100) . "\n";
} catch (\Exception $e) {
    echo "✗ Error en Deepseek: " . $e->getMessage() . "\n";
}

echo "\n";

// Probar Gemini
echo "=== Prueba de Gemini ===\n";
try {
    $client = new \GuzzleHttp\Client();
    $apiKey = config('services.gemini.key');
    
    $response = $client->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey", [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'contents' => [
                ['parts' => [['text' => 'Test']]],
            ],
        ],
        'timeout' => 10,
    ]);

    $data = json_decode($response->getBody(), true);
    echo "✓ Gemini respondió correctamente (status: " . $response->getStatusCode() . ")\n";
    echo "  Respuesta: " . substr($data['candidates'][0]['content']['parts'][0]['text'], 0, 100) . "\n";
} catch (\Exception $e) {
    echo "✗ Error en Gemini: " . $e->getMessage() . "\n";
}

echo "\n=== Fin de la prueba ===\n";
?>
