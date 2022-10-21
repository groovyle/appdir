<?php

namespace App\Http\Middleware;

use Closure;

class CheckUserStatus
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
		if($user = $request->user()) {
			if($user->is_blocked) {
				// Maybe the user got blocked mid app usage.
				return $this->redirectOut(
					'login',
					['blocked' => __('common.messages.your_account_was_blocked')]
				);
			}
		}

		return $next($request);
	}

	protected function redirectOut($route, $message = null) {
		// Log the user out and show an error message.
		\Auth::logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();

		$response = redirect()->route($route);
		if($message) {
			$response->withErrors($message);
		}

		return $response;
	}
}
