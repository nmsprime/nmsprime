<?php

namespace Modules\Ccc\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CccRedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard('ccc')->check()) {
            return redirect('customer');
        }

        return $next($request);
    }
}
