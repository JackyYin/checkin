<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
        if (Auth::guard('admin')->guest() && Auth::guard('manager')->guest()) {
            if ($request->ajax() || $request->wantsJson())
                return response('Unauthorized.', 401);
            return redirect()->route('admin.login');
        }
        return $next($request);
    }
}

