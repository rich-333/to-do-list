<?php

echo "=== Testing AI Providers ===\n\n";

// Test Groq
echo "1. Testing Groq...\n";
try {
    $controller = new \App\Http\Controllers\TaskListController();
    $method = new \ReflectionMethod($controller, 'getItemsFromGroq');
    $method->setAccessible(true);
    $result = $method->invoke($controller, "Generate 5 items for a shopping list");
    echo "✅ Groq Success:\n";
    var_dump($result);
} catch (\Exception $e) {
    echo "❌ Groq Error: " . $e->getMessage() . "\n\n";
}

// Test Deepseek
echo "\n2. Testing Deepseek...\n";
try {
    $controller = new \App\Http\Controllers\TaskListController();
    $method = new \ReflectionMethod($controller, 'getItemsFromDeepseek');
    $method->setAccessible(true);
    $result = $method->invoke($controller, "Generate 5 items for a shopping list");
    echo "✅ Deepseek Success:\n";
    var_dump($result);
} catch (\Exception $e) {
    echo "❌ Deepseek Error: " . $e->getMessage() . "\n\n";
}

// Test Gemini
echo "\n3. Testing Gemini...\n";
try {
    $controller = new \App\Http\Controllers\TaskListController();
    $method = new \ReflectionMethod($controller, 'getItemsFromGemini');
    $method->setAccessible(true);
    $result = $method->invoke($controller, "Generate 5 items for a shopping list");
    echo "✅ Gemini Success:\n";
    var_dump($result);
} catch (\Exception $e) {
    echo "❌ Gemini Error: " . $e->getMessage() . "\n\n";
}

echo "\n=== Config Check ===\n";
echo "GROQ_API_KEY: " . (env('GROQ_API_KEY') ? "✓ SET" : "✗ NOT SET") . "\n";
echo "DEEPSEEK_API_KEY: " . (env('DEEPSEEK_API_KEY') ? "✓ SET" : "✗ NOT SET") . "\n";
echo "GEMINI_KEY: " . (env('GEMINI_KEY') ? "✓ SET" : "✗ NOT SET") . "\n";

echo "\n=== Log Check ===\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = array_slice(file($logFile), -20);
    echo "Last 20 lines of laravel.log:\n";
    foreach ($lines as $line) {
        echo $line;
    }
} else {
    echo "No log file found.\n";
}
