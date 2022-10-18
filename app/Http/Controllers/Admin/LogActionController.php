<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\LogAction;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class LogActionController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(LogAction::class, 'log');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//

		$filters = get_filters(['keyword']);
		$opt_filters = optional($filters);
		$filter_count = 0;

		$query = LogAction::query();
		$query->with(['actor']);

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('entity_type', 'like', $like);
				$query->orWhere('entity_id', 'like', $like);
				$query->orWhere('related_type', 'like', $like);
				$query->orWhere('related_id', 'like', $like);
				$query->orWhere('action', 'like', $like);
				$query->orWhereHas('actor', function($query) use($like) {
					$query->where('name', 'like', $like);
					$query->orWhere('email', 'like', $like);
				});
				$query->orWhere('actor_name', 'like', $like);
			});
			$filter_count++;
		}

		$query->orderBy('at', 'desc');
		$query->orderBy('id', 'desc');

		$per_page = 50;
		$page = request()->input('page', 1);
		$goto_exact = request()->input('goto_exact');
		$goto_item = request()->input('goto_item');
		$goto_flash = request()->input('goto_flash') == 1;

		if($goto_exact) {
			$data['goto_item'] = $goto_exact;
		} elseif($goto_item) {
			$offset = find_item_offset_from_list_query($query, $goto_item);
			if($offset) {
				$target_page = ceil($offset / $per_page);
				if($target_page == $page) {
					$data['goto_item'] = $goto_item;
				} else {
					return self_redirect('goto_item', [
						'goto_exact' => $goto_item,
						'page' => $target_page
					]);
				}
			}
		}

		$list = $query->paginate($per_page, ['*'], 'page', $page);
		$list->appends($filters);

		// Redirect if over page. This can happen when e.g the last item in the
		// last page gets deleted. Redirect to last available page.
		if($list->total() > 0 && $page > $list->lastPage()) {
			return redirect()->to( $list->url($list->lastPage()) );
		}

		$data['list']			= $list;
		$data['total']			= LogAction::count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/log_action/index', $data);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  LogAction  $log
	 * @return \Illuminate\Http\Response
	 */
	public function show(LogAction $log)
	{
		$data = [
			'log'	=> $log,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/log_action/detail', $data);
	}

}
