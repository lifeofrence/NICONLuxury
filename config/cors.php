<?php

return [
    // Apply CORS to API endpoints and Sanctum CSRF route (if used)
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    // Allow all HTTP methods; tighten if you want to be explicit
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Allow origins driven by env with sensible fallbacks
    // Example env: CORS_ALLOWED_ORIGINS="https://nicon-luxury.vercel.app,http://localhost:3000"
    'allowed_origins' => (function () {
        $envOrigins = env('CORS_ALLOWED_ORIGINS', '');
        $list = array_filter(array_map('trim', explode(',', $envOrigins)));

        // Fallbacks for production and local dev
        if (empty($list)) {
            $list = [
            
                'http://localhost:3000',
                'http://127.0.0.1:3000',
                'https://niconluxury.jubileesystem.com',
            ];
        }
        return $list;
    })(),

    // Optional regex patterns for dynamic preview domains (e.g., Vercel previews)
    // Example env: CORS_ALLOWED_ORIGINS_PATTERNS="~^https://nicon-luxury-.*\\.vercel\\.app$~"
    'allowed_origins_patterns' => (function () {
        $envPatterns = env('CORS_ALLOWED_ORIGINS_PATTERNS', '');
        $list = array_filter(array_map('trim', explode(',', $envPatterns)));
        return $list;
    })(),

    // Allow common headers needed for APIs
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Accept', 'Origin'],

    // Headers exposed to the browser
    'exposed_headers' => ['Authorization', 'Location'],

    // Cache preflight for an hour
    'max_age' => 3600,

    // Enable only if using cookie-based auth (Sanctum SPA) and front-end sends credentials
    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),
];
