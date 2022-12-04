<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use App\User;
use App\Models\Prodi;
use App\Rules\ModelExists;
use App\DataManagers\LanguageManager as LangMan;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Validator;

use Bouncer;

class RegisterController extends Controller
{
	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles the registration of new users as well as their
	| validation and creation. By default this controller uses a trait to
	| provide this functionality without requiring any additional code.
	|
	*/

	use RegistersUsers;

	protected $broker;

	protected $usesVerification = null;

	/**
	 * Where to redirect users after registration.
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
		$this->middleware('guest');

		$this->usesVerification = config('auth.verify_email');
	}

	public function showRegistrationForm()
	{
		$data = [
			'prodis'	=> Prodi::all(),
			'lang_list'	=> LangMan::getTranslatedList(),
			'lang'		=> app()->getLocale(),
		];

		return view('auth.register', $data);
	}

	/**
	 * Handle a registration request for the application.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function register(Request $request)
	{
		$this->validator($request->all())->validate();

		event(new Registered($user = $this->create($request->all())));

		$this->guard()->login($user);

		return $this->registered($request, $user)
						?: redirect($this->redirectPath());
	}

	/**
	 * Get a validator for an incoming registration request.
	 *
	 * @param  array  $data
	 * @return \Illuminate\Contracts\Validation\Validator
	 */
	protected function validator(array $data)
	{
		return Validator::make($data, [
			'name'		=> ['required', 'string', 'max:255'],
			'email'		=> ['required', 'string', 'email', 'max:255', 'unique:users'],
			'password'	=> ['required', 'string', 'between:5,50', 'confirmed'],
			'prodi'		=> ['required', new ModelExists(Prodi::class)],
			'language'	=> ['required', Rule::in(LangMan::$languages)],
		], [], [
			'name'		=> __('frontend.auth.fields.name'),
			'email'		=> __('frontend.auth.fields.email'),
			'password'	=> __('frontend.auth.fields.password'),
			'prodi'		=> __('frontend.auth.fields.prodi'),
			'language'	=> __('frontend.auth.fields.language'),
		]);
	}

	/**
	 * Create a new user instance after a valid registration.
	 *
	 * @param  array  $data
	 * @return \App\User
	 */
	protected function create(array $data)
	{
		DB::beginTransaction();

		$result = true;
		$message = '';
		try {
			$user = new User([
				'name' => $data['name'],
				'email' => $data['email'],
				'password' => Hash::make($data['password']),
				'prodi_id' => $data['prodi'],
				'lang' => $data['language'],
			]);

			$result = $user->save();

			if($result) {
				$result = Bouncer::assign('mahasiswa')->to($user);
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$message = $e->getMessage();
		}

		if($result) {
			DB::commit();

			// Pass a message
			session()->flash('flash_message', [
				'message'	=> __('frontend.auth.messages.registration_successful'),
				'type'		=> 'success'
			]);

			return $user;
		} else {
			DB::rollback();

			// Pass a message
			$message = $message ? $message : __('frontend.auth.messages.registration_failed');
			session()->flash('flash_message', [
				'message'	=> $message,
				'type'		=> 'error'
			]);

			redirect()->back()->withInput()->withError($message);
			return;
		}
	}

	/**
	 * The user has been registered.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  mixed  $user
	 * @return mixed
	 */
	protected function registered(Request $request, $user)
	{
		/*if($this->usesVerification) {
			// Email is sent by a system event listener, no need to send it again
			// $user->sendEmailVerificationNotification();
			return redirect()->route('after_register.verify_first');
		}*/
	}

	protected function redirectTo() {
		return route('after_register');
	}

}
