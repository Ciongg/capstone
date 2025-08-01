<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'sender_name' => env('BREVO_SENDER_NAME', 'Formigo'),
        'sender_email' => env('BREVO_SENDER_EMAIL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_OAUTH_CLIENTID'),
        'client_secret' => env('GOOGLE_OAUTH_SECRET'),
        'redirect' => env('GOOGLE_OAUTH_REDIRECT'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'deepseek' => [
        'endpoint' => env('AZURE_DEEPSEEK_ENDPOINT'),
        'api_key' => env('AZURE_DEEPSEEK_KEY'),
    ],


];
