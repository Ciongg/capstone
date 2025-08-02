<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the API key from config
$apiKey = config('services.gemini.api_key');
echo "Using API key: " . $apiKey;

// Create a simple HTTP request to Gemini API
$response = \Illuminate\Support\Facades\Http::withHeaders([
    'Content-Type' => 'application/json',
])->timeout(20)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
    'contents' => [
        ['parts' => [['text' => 'Say hello world']]]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 100,
        'temperature' => 0.1,
    ]
]);

// Check response
if ($response->successful()) {
    echo "API call successful!\n";
    echo "Response: " . json_encode($response->json(), JSON_PRETTY_PRINT) . "\n";
} else {
    echo "API call failed!\n";
    echo "Error: " . $response->body() . "\n";
}
