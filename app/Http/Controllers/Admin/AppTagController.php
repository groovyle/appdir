<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppTag;

use App\Rules\NoSpaces;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class AppTagController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(AppTag::class, 'tag');
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

		$query = AppTag::withCount('apps')->withoutTrashed();

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
				// $filter_count++;
				break;
			case 'name':
			default:
				// Do nothing since default order from model is already by name
				break;
		}

		$per_page = 20;
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
		$data['total']			= AppTag::withoutTrashed()->count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/app_tag/index', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		//
		$back_url = null;
		if(Auth::user()->can('view-any', AppTag::class)) {
			$back_url = route('admin.app_tags.index');
		}

		$data = [
			'tag'		=> new AppTag,
			'is_edit'	=> false,
			'action'	=> route('admin.app_tags.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> 'list',
		];

		return view('admin/app_tag/edit', $data);
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

			if(Auth::user()->can('view-any', AppTag::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.app_tags.index', [
					'goto_item'		=> $store['tag']->name,
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  AppTag  $tag
	 * @return \Illuminate\Http\Response
	 */
	public function show(AppTag $tag)
	{
		$tag->loadCount('apps');

		$data = [
			'tag'	=> $tag,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/app_tag/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  AppTag  $tag
	 * @return \Illuminate\Http\Response
	 */
	public function edit(AppTag $tag)
	{
		//
		$back_url = null;

		if(Auth::user()->can('view', $tag)) {
			$back_url = route('admin.app_tags.show', ['tag' => $tag->name]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', AppTag::class)) {
			$back_url = route('admin.app_tags.index', ['goto_item' => $tag->name]);
		}

		$data = [
			'tag'		=> $tag,
			'is_edit'	=> true,
			'action'	=> route('admin.app_tags.update', ['tag' => $tag->name]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/app_tag/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  AppTag  $tag
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, AppTag $tag)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $tag);

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
			if($backto == 'list' && Auth::user()->can('view-any', AppTag::class)) {
				return redirect()->route('admin.app_tags.index', ['goto_item' => $tag->name, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $tag)) {
				return redirect()->route('admin.app_tags.show', ['tag' => $tag->name]);
			}

			return redirect()->back();
		}
	}

	protected function _store($request, $tag = NULL) {

		$is_edit = $tag instanceof AppTag;
		if(!$is_edit) {
			$tag = new AppTag;
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
				new NoSpaces,
				Rule::unique(AppTag::class, 'name')->ignore($tag),
			],
			'description'	=> ['nullable', 'string', 'max:500'],
		];

		$validData = $request->validate($rules);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			$tag->name			= $request->input('name');
			$tag->description	= $request->input('description');

			if(!$tag->slug) {
				// NOTE: slug is only generated once to prevent any duplicates
				$inc = 1;
				do {
					$tag->slug = Str::slug($tag->name . ($inc > 1 ? '-'.$inc : ''));
					$query = AppTag::where('slug', $tag->slug);
					if($is_edit) {
						$query->where('name', '<>', $tag->name);
					}
					$exists = $query->exists();
					$inc++;
				} while($exists);
			}

			$result = $tag->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'tag');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  AppTag  $tag
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, AppTag $tag)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $tag->delete();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if($result) {
			// DB::commit();
			DB::rollback();

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

		$redirect = null;
		$backto = request()->query('backto');
		if(Auth::user()->can('view-any', AppTag::class)) {
			$redirect = route('admin.app_tags.index');
		}
		if(!$redirect || $backto == 'back') {
			$redirect = url()->previous();
		}

		if(!$request->ajax()) {
			if($result) {
				return redirect($redirect);
			} else {
				return redirect()->back()->withErrors($messages);
			}
		} else {
			if($result) {
				return response()->json([
					'status'	=> 'OK',
					'redirect'	=> $redirect,
				], 200);
			} else {
				return response()->json([
					'status'	=> 'ERROR',
					'message'	=> \Arr::get($messages, 0),
				], 500);
			}
		}
	}
}
