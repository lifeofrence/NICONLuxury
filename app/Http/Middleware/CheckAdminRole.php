<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next, $role = 'admin')
    {
        $user = $request->user();
        if (!$user || $user->role !== $role) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}
