<?php

// CustomAuthMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Helpers\ApiResponse;
use Illuminate\Support\Facades\Log;

class CustomAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if (!auth('sanctum')->check()) {
            return ApiResponse::error(401, 'Unauthorized', 'Authentication required.');
        }
        $request->user = auth('sanctum')->user();
        $user = $request->user ;
        if ($user) {
            $user->update(['last_activity' => now()]);
        }



        return $next($request);
    }


}

