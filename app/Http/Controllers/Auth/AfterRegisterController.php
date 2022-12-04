<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class AfterRegisterController extends Controller
{

	public function afterRegister() {
		return view('auth.after-register');
	}

	public function verifyFirst() {
		return view('auth.after-register-verify-notice');
	}

}
