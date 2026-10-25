<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Holoo / Nila Web Service
    |--------------------------------------------------------------------------
    |
    | Configure the base URL exactly as documented by Holoo, for example:
    | http://192.168.1.20:8080/TncHoloo/api
    |
    | Keep this endpoint on a trusted LAN/VPN and never expose the Holoo
    | service port directly to the public internet.
    |
    */

    'enabled' => (bool) env('NILA_ENABLED', false),
    'base_url' => env('NILA_BASE_URL', ''),
    'username' => env('NILA_USERNAME', ''),
    'password' => env('NILA_PASSWORD', ''),
    'database' => env('NILA_DATABASE', ''),

    'timeout' => max(3, (int) env('NILA_TIMEOUT', 20)),
    'page_size' => min(500, max(1, (int) env('NILA_PAGE_SIZE', 100))),
    'max_pages' => min(10000, max(1, (int) env('NILA_MAX_PAGES', 1000))),

    // Holoo exposes SellPrice, SellPrice2 ... SellPrice10 and selpricekarton.
    'price_field' => env('NILA_PRICE_FIELD', 'SellPrice'),

    // Keep at 1 until you confirm Holoo and Janan use the same currency unit.
    'price_multiplier' => (float) env('NILA_PRICE_MULTIPLIER', 1),

    // Products are imported hidden by default because the Holoo product list
    // does not provide storefront visibility and Janan manages product media.
    'active_by_default' => (bool) env('NILA_ACTIVE_BY_DEFAULT', false),
];
