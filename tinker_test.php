// Test AI providers from tinker
$groq = config('services.groq.key');
$deepseek = config('services.deepseek.key');
$gemini = config('services.gemini.key');

echo "=== API Keys Loaded ===\n";
echo "GROQ: " . ($groq ? "✓ " . substr($groq, 0, 20) . "..." : "✗ NOT SET") . "\n";
echo "DEEPSEEK: " . ($deepseek ? "✓ " . substr($deepseek, 0, 20) . "..." : "✗ NOT SET") . "\n";
echo "GEMINI: " . ($gemini ? "✓ " . substr($gemini, 0, 20) . "..." : "✗ NOT SET") . "\n\n";

// Try Groq
echo "Testing Groq API...\n";
try {
    $client = new \GuzzleHttp\Client();
    $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
        'headers' => [
            'Authorization' => "Bearer $groq",
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'model' => 'mixtral-8x7b-32768',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => 'List 3 items'],
            ],
            'temperature' => 0.7,
            'max_tokens' => 100,
        ],
    ]);
    echo "✅ Groq Response: " . $response->getStatusCode() . "\n";
} catch (\Exception $e) {
    echo "❌ Groq Error: " . $e->getMessage() . "\n";
}
