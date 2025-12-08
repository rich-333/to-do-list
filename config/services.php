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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'groq' => [
        'key' => env('GROQ_API_KEY'),
        'base_url' => 'https://api.groq.com/openai/v1',
        // Modelo por defecto y lista de fallback (CSV en .env)
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'fallbacks' => array_filter(array_map('trim', explode(',', env('GROQ_MODEL_FALLBACKS', 'mixtral-8x7b-32768,llama-3.1-70b-versatile')))),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
        'base_url' => 'https://api.deepseek.com',
        'model' => env('DEEPSEEK_MODEL', 'deepseek-chat'),
    ],

    'gemini' => [
        'key' => env('GEMINI_KEY'),
        'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
    ],

];
