<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;

class CheckTokenExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if expired
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            $token->delete(); // Invalidate
            return response()->json(['message' => 'Token expired'], 401);
        }

        // Refresh expiration to 1 hour from now (sliding)
        $token->forceFill([
            'expires_at' => now()->addHour()
        ])->save();

        return $next($request);
    }
}
