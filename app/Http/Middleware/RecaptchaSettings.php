<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class RecaptchaSettings
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		config([
			'googlerecaptchav2.theme'		=> theme_timely()[0] == 'dark' ? 'dark' : 'light',
			'googlerecaptchav2.language'	=> session('locale'),
		]);

		return $next($request);
	}
}
