<?php

return [
    // Apply CORS only to API endpoints (adjust if you have other routes)
    'paths' => ['api/*'],

    // Allow all HTTP methods; tighten if you want to be explicit
    'allowed_methods' => ['*'],

    // Comma-separated list of origins from .env; falls back to '*'
    // Example: CORS_ALLOWED_ORIGINS="https://niconluxury.jubileesystem.com,https://admin.niconluxury.com,http://localhost:3000"
    'allowed_origins' => (function () {
        $origins = array_filter(array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', ''))));
        return !empty($origins) ? $origins : ['*'];
    })(),

    // Optional regex pattern from .env for subdomains (e.g., ^https:\/\/(.*\.)?niconluxury\.com$)
    'allowed_origins_patterns' => array_filter([env('CORS_ALLOWED_ORIGINS_PATTERN', '')]),

    // Allow any headers; tighten if necessary
    'allowed_headers' => ['*'],

    // Headers exposed to the browser (Authorization is commonly safe to expose)
    'exposed_headers' => ['Authorization'],

    // Preflight cache age in seconds
    'max_age' => 0,

    // Only set to true if you are using cookie-based auth from a browser (Sanctum SPA)
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),
];