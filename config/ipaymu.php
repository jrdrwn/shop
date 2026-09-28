<?php

return [
    'va' => env('IPAYMU_VA', ''),
    'api_key' => env('IPAYMU_API_KEY', ''),
    'callback_base_url' => env('IPAYMU_CALLBACK_BASE_URL'),
    'ca_bundle' => env('IPAYMU_CA_BUNDLE'),
    'is_production' => env('IPAYMU_IS_PRODUCTION', false),
    'api_url' => env('IPAYMU_IS_PRODUCTION', false)
        ? 'https://my.ipaymu.com/api/v2'
        : 'https://sandbox.ipaymu.com/api/v2',
];
