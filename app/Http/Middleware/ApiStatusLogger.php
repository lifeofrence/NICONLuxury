<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ApiStatusLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            $path = $request->path();
            // Only log API paths
            if (str_starts_with($path, 'api/')) {
                $endpoint = strtoupper($request->method()) . ' /' . $path;
                $statusCode = (string) $response->getStatusCode();
                $route = $request->route();
                $action = method_exists($route, 'getActionName') ? $route->getActionName() : 'unknown';
                $file = $action;

                $list = Cache::get('api_status_list', []);
                $list[] = [
                    'endpoint' => $endpoint,
                    'result' => $statusCode,
                    'file' => $file,
                    'timestamp' => now()->toDateTimeString(),
                ];
                if (count($list) > 50) {
                    $list = array_slice($list, -50);
                }
                // Clear every 5 minutes
                Cache::put('api_status_list', $list, 300);
            }
        } catch (\Throwable $e) {
            // Silent on logging failures
        }

        return $response;
    }
}