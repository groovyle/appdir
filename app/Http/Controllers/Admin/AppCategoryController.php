<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppCategory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AppCategoryController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//

		$filters = get_filters(['keyword', 'sort_by'], [
			'sort_by'	=> 'name',
		]);
		$opt_filters = optional($filters);
		$filter_count = 0;

		$query = AppCategory::withCount('apps')->withoutTrashed();

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('description', 'like', $like);
			});
			$filter_count++;
		}
		switch($opt_filters['sort_by']) {
			case 'apps':
				// Don't need to worry about default order since it's added last
				// (apparently global scopes are added last)
				$query->orderBy('apps_count', 'desc');
				$filter_count++;
				break;
			case 'name':
			default:
				// Do nothing since default order from model is already by name
				break;
		}

		$per_page = 20;
		$page = request()->input('page', 1);
		$goto_item = request()->input('goto_item');
		$goto_flash = request()->input('goto_flash') == 1;

		if($goto_item) {
			$offset = find_item_offset_from_list_query($query, $goto_item);
			if($offset) {
				$page = ceil($offset / $per_page);
				$data['goto_item'] = $goto_item;
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
		$data['total']			= AppCategory::count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/app_category/index', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
		$data = [
			'cat'		=> new AppCategory,
			'is_edit'	=> false,
			'action'	=> route('admin.app_categories.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> route('admin.app_categories.index'),
			'backto'	=> 'list',
		];

		return view('admin/app_category/edit', $data);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request);

		if(is_object($store)) {
			// Presumably a Response object
			return $store;
		}

		$result = $store['result'];
		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.create_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.create_successful'),
				'type'		=> 'success'
			]);

			// Scroll to the just added item
			return redirect()->route('admin.app_categories.index', [
				'goto_item'		=> $store['cat']->id,
				'goto_flash'	=> 1,
			]);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  AppCategory  $cat
	 * @return \Illuminate\Http\Response
	 */
	public function show(AppCategory $cat)
	{
		$cat->loadCount('apps');

		$data = [
			'cat'	=> $cat,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/app_category/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  AppCategory  $cat
	 * @return \Illuminate\Http\Response
	 */
	public function edit(AppCategory $cat)
	{
		//
		$back_url = route('admin.app_categories.show', ['cat' => $cat->id]);
		$backto = request()->query('backto');
		if($backto == 'list') {
			$back_url = route('admin.app_categories.index', ['goto_item' => $cat->id]);
		}

		$data = [
			'cat'		=> $cat,
			'is_edit'	=> true,
			'action'	=> route('admin.app_categories.update', ['cat' => $cat->id]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/app_category/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  AppCategory  $cat
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, AppCategory $cat)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $cat);

		if(is_object($store)) {
			// Presumably a Response object
			return $store;
		}

		$result = $store['result'];
		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_successful'),
				'type'		=> 'success'
			]);

			$backto = $request->input('backto');
			if($backto == 'list') {
				return redirect()->route('admin.app_categories.index', ['goto_item' => $cat->id, 'goto_flash' => 1]);
			} else {
				return redirect()->route('admin.app_categories.show', ['cat' => $cat->id]);
			}
		}
	}

	protected function _store($request, $cat = NULL) {

		$is_edit = $cat instanceof AppCategory;
		if(!$is_edit) {
			$cat = new AppCategory;
		}

		$user = $request->user();
		$user_id = $user->id;

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'name'			=> [
				'required',
				'max:100',
				Rule::unique(AppCategory::class, 'name')->ignore($cat),
			],
			'description'	=> ['nullable', 'string', 'max:500'],
		];

		$validData = $request->validate($rules);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			$cat->name			= $request->input('name');
			$cat->description	= $request->input('description');

			if(!$cat->slug) {
				// NOTE: slug is only generated once to prevent any duplicates
				$inc = 1;
				do {
					$cat->slug = Str::slug($cat->name . ($inc > 1 ? '-'.$inc : ''));
					$query = AppCategory::where('slug', $cat->slug);
					if($is_edit) {
						$query->where('id', '<>', $cat->id);
					}
					$exists = $query->exists();
					$inc++;
				} while($exists);
			}

			$result = $cat->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'cat');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  AppCategory  $cat
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, AppCategory $cat)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $cat->delete();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if($result) {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.delete_successful'),
				'type'		=> 'success'
			]);
		} else {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.delete_failed'),
				'type'		=> 'danger'
			]);
		}

		if(!$request->ajax()) {
			if($result) {
				return redirect()->back();
			} else {
				return redirect()->back()->withErrors($messages);
			}
		} else {
			if($result) {
				return response('OK', 200);
			} else {
				return response()->json([
					'status'	=> 'ERROR',
					'message'	=> \Arr::get($messages, 0),
				], 500);
			}
		}
	}
}
