<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Support\Facades\Auth;

class Bot
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $guard = 'bot';

        if (Auth()->guard($guard)->check()) {
            Auth()->shouldUse($guard);

            return $next($request);
        }

        if ($request->header('Accept') == 'text/plain') {
            return response("Unauthenticated Bot.", 401);
        }

        return response()->json([
            "reply_message" => [
                "auth" => [
                    "Unauthenticated Bot."
                ]
            ]
        ], 401);
    }
}

