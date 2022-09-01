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
		// TODO: if user is admin/verifier/owner, allow viewing an unpublished app
		// as a preview

		$app = App::getFrontendItem($slug);

		$data = [];
		$data['app'] = $app;

		return view('app/page', $data);
	}

	public function preview(string $slug) {
		$app = App::getFrontendItem($slug);

		$data = [];
		$data['app'] = $app;

		return view('app/preview', $data);
	}

}
