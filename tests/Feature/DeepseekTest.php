<?php

use Illuminate\Support\Facades\Http;

it('can call the DeepSeek API and get a response', function () {
    $endpoint = rtrim(config('services.deepseek.endpoint'), '/');
    $apiKey = config('services.deepseek.api_key');
    $modelName = "DeepSeek-R1-0528";
    $apiVersion = "2024-05-01-preview";
    $apiUrl = "{$endpoint}/openai/deployments/{$modelName}/chat/completions?api-version={$apiVersion}";

    $payload = [
        'messages' => [
            ['role' => 'system', 'content' => 'You are a professional assistant.'],
            ['role' => 'user', 'content' => 'Say hello world']
        ],
        'max_tokens' => 100,
        'temperature' => 0.1
    ];

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'api-key' => $apiKey,
    ])->timeout(20)->post($apiUrl, $payload);

    expect($response->successful())->toBeTrue();
    expect($response->json())->toHaveKey('choices');
    expect($response['choices'][0]['message']['content'] ?? null)->not->toBeEmpty();
})->group('integration');
