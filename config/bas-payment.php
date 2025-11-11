<?php

return [
    /*
    |--------------------------------------------------------------------------
    | BAS Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the BAS Payment Gateway SDK.
    | Make sure to set these values in your .env file.
    |
    */

    'base_url' => env('BAS_BASE_URL', 'https://api.basgate.com'),
    'client_id' => env('BAS_CLIENT_ID'),
    'client_secret' => env('BAS_CLIENT_SECRET'),
    'app_id' => env('BAS_APP_ID'),
    'merchant_key' => env('BAS_MERCHANT_KEY'),
    
    // IV must be exactly 16 bytes for AES-256-CBC
    // This is a constant value provided by BAS for all clients
    'iv' => env('BAS_IV', '@@@@&&&&####$$$$'),
    
    'environment' => env('BAS_ENVIRONMENT', 'staging'),

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    'token_endpoint' => env('BAS_TOKEN_ENDPOINT', '/api/v1/auth/token'),
    'refund_payment_endpoint' => env('BAS_REFUND_PAYMENT_ENDPOINT', '/api/v1/merchant/sdk-payment/reverse-payment/execute'),
    'transaction_status_endpoint' => env('BAS_TRANSACTION_STATUS_ENDPOINT', '/api/v1/merchant/sdk-payment/get-transaction-status'),
    'transaction_initiate_endpoint' => env('BAS_TRANSACTION_INITIATE_ENDPOINT', '/api/v1/merchant/sdk-payment/initiate-transaction'),
];
