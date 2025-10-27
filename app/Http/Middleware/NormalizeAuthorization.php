<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class NormalizeAuthorization
{
    public function handle(Request $request, Closure $next)
    {
        // Only apply on API routes and when Authorization header is absent
        if ($request->is('api/*') && !$request->headers->has('Authorization')) {
            // Some servers place the header into server variables
            $serverAuth = $request->server('HTTP_AUTHORIZATION')
                ?? $request->server('REDIRECT_HTTP_AUTHORIZATION')
                ?? null;

            // Alternate headers clients might send
            $xAuth = $request->header('X-Authorization')
                ?? $request->header('X-Auth-Token')
                ?? $request->header('X-API-Key')
                ?? $request->header('Bearer-Token');

            // Token passed via query/body (last resort)
            $tokenParam = $request->query('token') ?? $request->input('token') ?? $request->query('bearer_token');

            if ($serverAuth) {
                // Use the Authorization value as-is from server vars
                $request->headers->set('Authorization', $serverAuth);
            } elseif ($xAuth) {
                // Normalize to Bearer format if needed
                $authHeader = str_starts_with($xAuth, 'Bearer ') ? $xAuth : ('Bearer ' . $xAuth);
                $request->headers->set('Authorization', $authHeader);
            } elseif ($tokenParam) {
                // Construct Authorization header from token param
                $request->headers->set('Authorization', 'Bearer ' . $tokenParam);
            }
        }

        return $next($request);
    }
}
