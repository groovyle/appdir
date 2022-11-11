<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\DataManagers\LanguageManager as LangMan;

class LoginController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles authenticating users for the application and
	| redirecting them to your home screen. The controller uses a trait
	| to conveniently provide its functionality to your applications.
	|
	*/

	use AuthenticatesUsers {
		logout as _logout;
	}

	/**
	 * Where to redirect users after login.
	 *
	 * @var string
	 */
	protected $redirectTo = RouteServiceProvider::HOME;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('guest')->except('logout');
	}

	/**
	 * Override the default login method to add additional checks.
	 *
	 * Handle a login request to the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
	 *
	 * @throws \Illuminate\Validation\ValidationException
	 */
	public function login(Request $request)
	{
		$this->validateLogin($request);

		// If the class is using the ThrottlesLogins trait, we can automatically throttle
		// the login attempts for this application. We'll key this by the username and
		// the IP address of the client making these requests into this application.
		if (method_exists($this, 'hasTooManyLoginAttempts') &&
			$this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);

			return $this->sendLockoutResponse($request);
		}

		/*if ($this->attemptLogin($request)) {
			return $this->sendLoginResponse($request);
		}*/
		// This section is the only change
		if ($this->guard()->validate($this->credentials($request))) {
			$user = $this->guard()->getLastAttempted();

			// Make sure credentials are correct
			$validated = $this->guard()->getProvider()->validateCredentials($user, $this->credentials($request));
			if($validated) {
				// Make sure user is not blocked
				if($user->is_blocked) {
					// Increment the failed login attempts and redirect back to the
					// login form with an error message.
					$this->incrementLoginAttempts($request);
					return redirect()
						->route('login_error')
						// ->withInput($request->only($this->username(), 'remember'))
						->withErrors(['blocked' => __('common.messages.your_account_was_blocked')]);
				}

				if($this->attemptLogin($request)) {
					// Send the normal successful login response
					return $this->sendLoginResponse($request);
				}
			}
		}

		// If the login attempt was unsuccessful we will increment the number of attempts
		// to login and redirect the user back to the login form. Of course, when this
		// user surpasses their maximum number of attempts they will get locked out.
		$this->incrementLoginAttempts($request);

		return $this->sendFailedLoginResponse($request);
	}

	/**
	 * The user has been authenticated.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  mixed  $user
	 * @return mixed
	 */
	protected function authenticated(Request $request, $user)
	{
		// Set locale
		if(in_array($user->lang, LangMan::$languages)) {
			session(['locale' => $user->lang]);
		}
	}


	/**
	 * Log the user out of the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
		// Store user locale to maintain language even after logging out
		$user = \Auth::user();
		if($user)
			$this->user_lang = $user->lang;

		return $this->_logout($request);
	}

	/**
	 * The user has logged out of the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	protected function loggedOut(Request $request)
	{
		if(!empty($this->user_lang) && in_array($this->user_lang, LangMan::$languages))
			session(['locale' => $this->user_lang]);

		// If the user was on admin, redirect to login form instead...?
		$was_on_admin = in_admin_panel( url()->previous() );
		if($was_on_admin) {
			// return redirect()->route('login');
			return redirect()->route('admin');
		} else {
			// If on portal just go back
			// return redirect()->back();

			// Actually don't go back, maybe the app page needs authorization
			return redirect()->route('index');
		}
	}

	public function errorPage(Request $request) {
		$data = [];

		return view('auth.error-page');
	}
}
