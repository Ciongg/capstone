<?php

use Illuminate\Support\Facades\Http;

it('can call the Gemini API and get a response', function () {
    $apiKey = config('services.gemini.api_key');
    $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Say hello world']
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 100,
            'temperature' => 0.1,
        ]
    ];

    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])->timeout(20)->post($endpoint, $payload);

    expect($response->successful())->toBeTrue();
    expect($response->json())->not->toBeEmpty();
})->group('integration');
