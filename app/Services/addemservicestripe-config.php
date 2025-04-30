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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
        'currency' => env('STRIPE_CURRENCY', 'usd'),
        'endpoint' => env('STRIPE_API_ENDPOINT', 'https://api.stripe.com'),
    ],

    'cora' => [
        'api_key' => env('CORA_API_KEY'),
        'client_id' => env('CORA_CLIENT_ID'),
        'client_secret' => env('CORA_CLIENT_SECRET'),
        'webhook_secret' => env('CORA_WEBHOOK_SECRET'),
        'endpoint' => env('CORA_API_ENDPOINT', 'https://api.cora.com.br'),
    ],

];
