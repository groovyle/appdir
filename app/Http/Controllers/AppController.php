<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\Http\Request;

class AppController extends Controller
{
	//

	public function index() {

		$data = [];
		$data['apps'] = App::frontend()->get();

		return view('app/index', $data);
	}

	public function page(string $slug) {
		$app = App::frontendItem($slug);

		$data = [];
		$data['app'] = $app;

		return view('app/page', $data);
	}

	public function preview(string $slug) {
		$app = App::frontendItem($slug);

		$data = [];
		$data['app'] = $app;

		return view('app/preview', $data);
	}

}
