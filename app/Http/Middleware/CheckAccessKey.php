<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = str($request->header('Authorization'))
            ->after('Bearer ')
            ->trim();

        if (blank($token)) {
            return response()->json(['message' => 'Access key required'], 401);
        }

        $user = User::where('access_key', $token)->first();

        if (! $user) {
            return response()->json(['message' => 'Invalid access key'], 401);
        }

        // Inject user ke request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
