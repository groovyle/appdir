<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\App;
use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

	public function profile($user_id) {
		$user = User::getFrontendItem($user_id, false, false);

		$this->authorize('view-public', $user);

		$data = [];
		$self = Auth::user();
		$filter_form = request('f');

		$query = App::frontend();
		$query->with(['thumbnail']);
		$query->where('owner_id', $user->id);
		$apps_total = $query->count();

		$apps_filter_count = 0;
		if($filter_form == 'apps') {
			if($search = trim(request('s'))) {
				$str = escape_mysql_like_str($search);
				$like = '%'.$str.'%';
				$query->where(function($query) use($like) {
					$query->where('name', 'like', $like);
					$query->orWhere('short_name', 'like', $like);
					$query->orWhere('description', 'like', $like);
				});

				$apps_filter_count++;
			}
		}

		$query->orderBy('name');
		$query->orderBy('short_name');

		$apps = $query->get();

		$data['apps_total'] = $apps_total;
		$data['apps'] = $apps;
		$data['user'] = $user;
		$data['self'] = $self;
		$data['is_self'] = $user->is_me;
		$data['apps_filter_count'] = $apps_filter_count;

		return view('user/profile', $data);
	}

}