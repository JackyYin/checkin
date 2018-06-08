<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Support\Facades\Auth;

class User
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
        $guard = "api";

        $request->headers->set('Authorization', 'Bearer '.$request->headers->get('User-Authorization'));

        if (Auth()->guard($guard)->check()) {
            Auth()->shouldUse($guard);

            return $next($request);
        }

        return $next($request);
    }
}

