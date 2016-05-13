<?php namespace Modules\Ccc\Http\Middleware;

use Closure;

class CccBaseMiddleware {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // TODO: check and verify login

    	return $next($request);
    }

}
