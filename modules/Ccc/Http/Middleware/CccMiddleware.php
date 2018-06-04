<?php namespace Modules\Ccc\Http\Middleware;

use Auth, Closure;
use App\Exceptions\AuthException;


class CccMiddleware {

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (Auth::guard('ccc')->check()) {
			return redirect('customer');
		}

		return $next($request);
	}

}
