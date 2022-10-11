<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\Prodi;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ProdiController extends Controller
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

		$query = Prodi::withCount('users')->withoutTrashed();

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('short_name', 'like', $like);
				$query->orWhere('description', 'like', $like);
			});
			$filter_count++;
		}
		switch($opt_filters['sort_by']) {
			case 'users':
				// Don't need to worry about default order since it's added last
				// (apparently global scopes are added last)
				$query->orderBy('users_count', 'desc');
				// $filter_count++;
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
		$data['total']			= Prodi::withoutTrashed()->count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/prodi/index', $data);
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
			'prodi'		=> new Prodi,
			'is_edit'	=> false,
			'action'	=> route('admin.prodi.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> route('admin.prodi.index'),
			'backto'	=> 'list',
		];

		return view('admin/prodi/edit', $data);
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
			return redirect()->route('admin.prodi.index', [
				'goto_item'		=> $store['prodi']->id,
				'goto_flash'	=> 1,
			]);
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Prodi  $prodi
	 * @return \Illuminate\Http\Response
	 */
	public function show(Prodi $prodi)
	{
		$prodi->loadCount('users');

		$data = [
			'prodi'	=> $prodi,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/prodi/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Prodi  $prodi
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Prodi $prodi)
	{
		//
		$back_url = route('admin.prodi.show', ['prodi' => $prodi->id]);
		$backto = request()->query('backto');
		if($backto == 'list') {
			$back_url = route('admin.prodi.index', ['goto_item' => $prodi->id]);
		}

		$data = [
			'prodi'		=> $prodi,
			'is_edit'	=> true,
			'action'	=> route('admin.prodi.update', ['prodi' => $prodi->id]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/prodi/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Prodi  $prodi
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Prodi $prodi)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $prodi);

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
				return redirect()->route('admin.prodi.index', ['goto_item' => $prodi->id, 'goto_flash' => 1]);
			} else {
				return redirect()->route('admin.prodi.show', ['prodi' => $prodi->id]);
			}
		}
	}

	protected function _store($request, $prodi = NULL) {

		$is_edit = $prodi instanceof Prodi;
		if(!$is_edit) {
			$prodi = new Prodi;
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
				Rule::unique(Prodi::class, 'name')->ignore($prodi),
			],
			'short_name'	=> [
				'nullable',
				'max:20',
				Rule::unique(Prodi::class, 'short_name')->ignore($prodi),
			],
			'description'	=> ['nullable', 'string', 'max:500'],
		];

		$validData = $request->validate($rules);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			$prodi->name		= $request->input('name');
			$prodi->short_name	= $request->input('short_name');
			$prodi->description	= $request->input('description');

			// Loop slug till unique
			$inc = 1;
			do {
				$prodi->slug = Str::slug($prodi->name . ($inc > 1 ? '-'.$inc : ''));
				$query = Prodi::where('slug', $prodi->slug);
				if($is_edit) {
					$query->where('id', '<>', $prodi->id);
				}
				$exists = $query->exists();
				$inc++;
			} while($exists);

			$result = $prodi->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'prodi');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Prodi  $prodi
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Prodi $prodi)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $prodi->delete();
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
