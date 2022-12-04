<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class AfterVerificationController extends Controller
{

	public function afterVerify() {
		return view('auth.after-verify');
	}

}
