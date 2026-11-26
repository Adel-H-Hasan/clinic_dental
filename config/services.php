<?php

return [
    'medassist_ai' => [
        'url' => env('MEDASSIST_AI_URL', 'http://127.0.0.1:8001'),
        'token' => env('MEDASSIST_AI_TOKEN'),
        'timeout' => (int) env('MEDASSIST_AI_TIMEOUT', 900),
    ],

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

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        // Explicit opt-in; never activate a potentially billed API merely because a key exists.
        'demo_review' => (bool) env('GEMINI_REVIEW_ENABLED', false),
        'model' => env('GEMINI_MODEL', 'gemini-3.1-flash-lite'),
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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'jod'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

];
