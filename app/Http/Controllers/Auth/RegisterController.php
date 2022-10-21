<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use App\SystemUser;
use App\Rules\FQDN;
use App\SystemDataProviders\SystemDataBroker;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use Illuminate\Support\Facades\Validator;

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
	public function __construct(SystemDataBroker $broker)
	{
		$this->middleware('guest');

		$this->broker = $broker;
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
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
			'password' => ['required', 'string', 'min:5', 'max:50', 'confirmed'],
			'domain' => ['required', new FQDN([], TRUE)],
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

		$user = new User([
			'name' => $data['name'],
			'email' => $data['email'],
			'password' => Hash::make($data['password']),
		]);

		$result = $user->save();
		$message = '';

		// Generate a random username
		$prefix = random_string(4, 'abcdefghijklmnopqrstuvwxyz');
		$username = $prefix . random_string(6, '1234567890');

		if($result) {
			$sysuser = new SystemUser([
				'username'	=> $username,
				'password'	=> encrypt($data['password']),
				'domain'	=> $data['domain'],
				'prefix'	=> $prefix,
			]);
			$result = $user->system()->save($sysuser);
		}

		$result_remote = NULL;
		if($result) {
			$remote = $this->broker->createUser($data['domain'], $username, $data['password'], $data['name']);
			$result = $result_remote = $remote['status'];
			if(!$result) {
				$message = $remote['message'];
			}
		}

		if($result) {
			DB::commit();

			// Pass a message...?
			session()->flash('flash_message', [
				'message'	=> __('admin.user.registration_successful'),
				'type'		=> 'success'
			]);
		} else {
			DB::rollback();

			// Pass a message...?
			$message = $message ? $message : __('admin.user.registration_failed');
			session()->flash('flash_message', [
				'message'	=> $message,
				'type'		=> 'error'
			]);

			if($result_remote === TRUE) {
				$this->broker->deleteUser($data['domain'], $username);
			}

			redirect()->back()->withInput()->withError($message);
		}

		return $user;
	}
}
