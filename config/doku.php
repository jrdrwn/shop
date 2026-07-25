<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Doku Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi payment gateway Doku.
    | Pastikan CLIENT_ID dan SECRET_KEY diisi di .env.
    |
    */

    'client_id' => env('DOKU_CLIENT_ID'),
    'secret_key' => env('DOKU_SECRET_KEY'),

    'is_production' => env('DOKU_IS_PRODUCTION', false),

    'api_url' => env('DOKU_IS_PRODUCTION', false)
        ? 'https://api.doku.com'
        : 'https://api-sandbox.doku.com',
];
