// Test rápido de conexión a APIs (ejecutar en php artisan tinker)

echo "=== Verificando configuración ===\n";
echo "Groq Key: " . (strlen(config('services.groq.key')) > 5 ? "✓ Configurada" : "✗ No configurada") . "\n";
echo "Deepseek Key: " . (strlen(config('services.deepseek.key')) > 5 ? "✓ Configurada" : "✗ No configurada") . "\n";
echo "Gemini Key: " . (strlen(config('services.gemini.key')) > 5 ? "✓ Configurada" : "✗ No configurada") . "\n\n";

// Test simple de Groq
echo "=== Test Groq ===\n";
$client = new \GuzzleHttp\Client();
try {
    $res = $client->post('https://api.groq.com/openai/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . config('services.groq.key'),
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'mixtral-8x7b-32768',
            'messages' => [
                ['role' => 'user', 'content' => 'Responde: {"status": "ok"}'],
            ],
            'max_tokens' => 50,
        ],
        'timeout' => 10,
    ]);
    echo "✓ Groq OK (HTTP " . $res->getStatusCode() . ")\n";
    $data = json_decode($res->getBody(), true);
    echo "  Respuesta: " . substr($data['choices'][0]['message']['content'], 0, 80) . "\n";
} catch (\Exception $e) {
    echo "✗ Groq ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Deepseek ===\n";
try {
    $res = $client->post('https://api.deepseek.com/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . config('services.deepseek.key'),
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => 'Responde: {"status": "ok"}'],
            ],
            'max_tokens' => 50,
        ],
        'timeout' => 10,
    ]);
    echo "✓ Deepseek OK (HTTP " . $res->getStatusCode() . ")\n";
    $data = json_decode($res->getBody(), true);
    echo "  Respuesta: " . substr($data['choices'][0]['message']['content'], 0, 80) . "\n";
} catch (\Exception $e) {
    echo "✗ Deepseek ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Test Gemini ===\n";
try {
    $res = $client->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . config('services.gemini.key'), [
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'contents' => [
                ['parts' => [['text' => 'Responde: {"status": "ok"}']]],
            ],
        ],
        'timeout' => 10,
    ]);
    echo "✓ Gemini OK (HTTP " . $res->getStatusCode() . ")\n";
    $data = json_decode($res->getBody(), true);
    echo "  Respuesta: " . substr($data['candidates'][0]['content']['parts'][0]['text'], 0, 80) . "\n";
} catch (\Exception $e) {
    echo "✗ Gemini ERROR: " . $e->getMessage() . "\n";
}
