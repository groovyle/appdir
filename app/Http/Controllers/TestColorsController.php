<?php

namespace App\Http\Controllers;

use App\Models\ColorScheme;
use Illuminate\Http\Request;

class TestColorsController extends Controller
{
	//

	public function index(Request $request) {

		$query = ColorScheme::query();

		$chroma = $request->input('theme');
		switch($chroma) {
			case 'light':
				$query->light();
				break;
			case 'dark':
				$query->dark();
				break;
			case 'flexible':
				$query->flexible();
				break;
		}

		$search = trim($request->input('search'));
		if($search) {
			$like = escape_mysql_like_str($search);
			$search = '%'. $like .'%';
			$query->where(function($query) use($search) {
				$query->where('name', 'like', $search);
				$query->orWhere('description', 'like', $search);
				$query->orWhere('notes', 'like', $search);
			});

			$query->orderByRaw('(`name` like ?) desc', [$like.'%']);
		}

		$faved = $request->input('faved');
		if($faved) {
			$query->where('faved', $faved);
		}

		$schemes = $query->get();

		$data = [];
		$data['schemes'] = $schemes;

		return view('color_test', $data);
	}

}
