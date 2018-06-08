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

        return response()->json([
            "reply_message" => "Unauthenticated.",
        ], 401);
    }
}

