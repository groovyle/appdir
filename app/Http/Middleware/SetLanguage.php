<?php

namespace App\Http\Middleware;

use App;
use Auth;
use Illuminate\Support\Carbon;
use Closure;
use App\DataManagers\LanguageManager as LangMan;

class SetLanguage
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
		// Get available locales
		$locale = config('app.locale');
		$fallback_locale = config('app.fallback_locale');
		$user_locale = null;
		if(($user = Auth::user()) && $user->lang) {
			$user_locale = $user->lang;
		}

		// Store default locale first before overwriting
		config()->set('app.default_locale', $locale);


		// ===== Set

		// Use session so that guests can also change their language, albeit volatile
		if(!session()->has('locale')) {
			$use_locale = $user_locale ?: $locale;
			session(['locale' => $use_locale]);
		} else {
			$use_locale = session('locale');
		}

		// Set code outsourced so that it may be reused
		LangMan::setLocale($use_locale);

		return $next($request);
	}
}
