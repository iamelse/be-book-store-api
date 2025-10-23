<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Traits\APIResponse;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    use APIResponse;

    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return self::errorResponse('Unauthorized', 401);
        }

        if (!in_array($user->role, $roles)) {
            return self::errorResponse('Access denied. You do not have permission to access this resource.', 403);
        }

        return $next($request);
    }
}