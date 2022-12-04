<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;

class EnsureUserIsVerified
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $redirectToRoute
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 * @see \Illuminate\Auth\Middleware\EnsureEmailIsVerified
	 */
	public function handle($request, Closure $next, $redirectToRoute = null)
	{
		if (! $request->user() ||
			($request->user() instanceof MustVerifyEmail &&
			! $request->user()->is_verified)) {
			return $request->expectsJson()
					? abort(403, __('frontend.auth.messages.your_email_is_not_verified'))
					: Redirect::route($redirectToRoute ?: 'verification.notice');
		}

		return $next($request);
	}
}
