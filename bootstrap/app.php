<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Global CORS handling using config/cors.php
        $middleware->use([\Illuminate\Http\Middleware\HandleCors::class]);

        // Register route middleware aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role' => \App\Http\Middleware\CheckAdminRole::class,
        ]);

        // Ensure Authorization header reaches Sanctum even if server alters it - run very early
        $middleware->prepend(\App\Http\Middleware\NormalizeAuthorization::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return detailed JSON for unauthenticated API requests
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $authHeader = $request->header('Authorization', '');
                $hasAuth = $authHeader !== '';
                $hasBearer = str_starts_with($authHeader, 'Bearer ');

                return response()->json([
                    'message' => 'Unauthenticated.',
                    'path' => $request->path(),
                    'guard' => $e->guards()[0] ?? 'sanctum',
                    'hint' => $hasAuth
                        ? ($hasBearer
                            ? 'Bearer token missing, invalid, or expired.'
                            : 'Authorization header must be of type: Bearer {token}.')
                        : 'Missing Authorization header. Send Authorization: Bearer {token}.',
                ], 401);
            }
        });
    })->create();
