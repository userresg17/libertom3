<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // --- LIBERTOM EXTERNAL SERVICES ---

    'cora' => [
        'base_url' => env('CORA_BASE_URL', 'https://api.cora.com.br/v1'), // <-- VERIFICAR URL BASE NA DOC CORA
        'api_key' => env('CORA_API_KEY'), // Chave/Token Bearer para autenticação
        'client_id' => env('CORA_CLIENT_ID'), // Necessário se usar fluxo OAuth2
        'client_secret' => env('CORA_CLIENT_SECRET'), // Necessário se usar fluxo OAuth2
        'auth_url' => env('CORA_AUTH_URL'), // URL para obter token OAuth2, se Cora usar
        'pix_key' => env('CORA_PIX_KEY'), // Chave PIX principal da conta Libertom na Cora
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'base_url' => 'https://api.stripe.com',
    ],

    'twelve_data' => [
        'base_url' => env('TWELVE_DATA_BASE_URL', 'https://api.twelvedata.com'),
        'api_key' => env('TWELVE_DATA_API_KEY'),
    ],

    'exchangerate_api' => [
        'base_url' => env('EXCHANGERATE_API_BASE_URL', 'https://v6.exchangerate-api.com/v6'),
        'api_key' => env('EXCHANGERATE_API_KEY'),
    ],

    'sumsub' => [
        'base_url' => env('SUMSUB_BASE_URL', 'https://api.sumsub.com'),
        'app_token' => env('SUMSUB_APP_TOKEN'),
        'secret_key' => env('SUMSUB_SECRET_KEY'),
        'webhook_secret' => env('SUMSUB_WEBHOOK_SECRET'),
    ],

    'polygon' => [
        'rpc_url' => env('POLYGON_RPC_URL', 'https://polygon-rpc.com'),
        'chain_id' => env('POLYGON_CHAIN_ID', 137),
         'goldstay_contract' => env('GOLDSTAY_CONTRACT_ADDRESS'),
         // 'goldstay_abi_path' => storage_path('app/contracts/GoldStayABI.json'),
    ],

    'polygonscan' => [
         'base_url' => env('POLYGONSCAN_BASE_URL', 'https://api.polygonscan.com/api'),
         'api_key' => env('POLYGONSCAN_API_KEY'),
    ],

];