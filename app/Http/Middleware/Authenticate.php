<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * For API routes, return null so Laravel responds with 401 JSON
     * instead of attempting to redirect to a non-existent 'login' route.
     */
    protected function redirectTo($request)
    {
        // Treat any request to /api/* as an API request
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        // For non-API requests, redirect to 'login' only if it exists
        if (Route::has('login')) {
            return route('login');
        }

        // Fallback: no redirect, just return null (will produce 401)
        return null;
    }
}