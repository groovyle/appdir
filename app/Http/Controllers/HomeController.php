<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\App;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		// $this->middleware('auth');
	}

	/**
	 * Show the application dashboard.
	 *
	 * @return \Illuminate\Contracts\Support\Renderable
	 */
	public function index()
	{
		$data = [
			'total_apps'	=> App::frontend()->count(),
			'user'			=> Auth::user(),
		];

		return view('index', $data);
	}

	public function home()
	{
		return view('home');
	}
}
