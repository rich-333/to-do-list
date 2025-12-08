<?php
/**
 * Test script to verify /ai/suggest-items endpoint
 * Run from: php artisan tinker
 * Then: require 'TEST_ENDPOINT.php';
 */

use Illuminate\Http\Request;

echo "=== Testing /ai/suggest-items Endpoint ===\n\n";

// Simular una request POST a /ai/suggest-items
/** @var \Illuminate\Http\Request $request */
$request = Request::create('/', 'POST', [
    'title' => 'Test List for Shopping',
    'context' => 'For weekly groceries',
    'provider' => 'groq'
]);

// Cargar el controller
$controller = new \App\Http\Controllers\TaskListController();

// Simular autenticación
\Illuminate\Support\Facades\Auth::loginUsingId(1);

try {
    echo "📝 Request data:\n";
    echo "  - title: " . $request->get('title') . "\n";
    echo "  - context: " . $request->get('context') . "\n";
    echo "  - provider: " . $request->get('provider') . "\n\n";

    echo "🔄 Calling suggestItems()...\n\n";
    $response = $controller->suggestItems($request);

    echo "✅ Response received:\n";
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Data:\n";
    var_dump(json_decode($response->getContent(), true));

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}
