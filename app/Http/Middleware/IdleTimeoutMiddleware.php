<?php

namespace App\Http\Middleware;

use App\Http\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IdleTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = auth('sanctum')->user();


        if ($user) {
            $lastActivity = $user->last_activity;

            $idleTime = now()->diffInMinutes($lastActivity);

            if ($idleTime > 30) {
                Auth::logout();
                return ApiResponse::error(401, 'Unauthorized', 'Session expired due to inactivity');

            }
        }

        return $next($request);
    }
}
