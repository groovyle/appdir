<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Setting;

use App\Rules\NoSpaces;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class SettingController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(Setting::class, 'stt');
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

		$query = Setting::query();

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('key', 'like', $like);
				$query->orWhere('description', 'like', $like);
			});
			$filter_count++;
		}
		$query->orderBy('key');

		$goto_item = request()->input('goto_item');
		$goto_flash = request()->input('goto_flash') == 1;

		if($goto_item) {
			$data['goto_item'] = $goto_item;
		}

		$list = $query->get();

		$data['list']			= $list;
		$data['total']			= Setting::count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= $filters;

		return view('admin/setting/index', $data);
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
		if(Auth::user()->can('view-any', Setting::class)) {
			$back_url = route('admin.settings.index');
		}

		$data = [
			'stt'		=> new Setting,
			'is_edit'	=> false,
			'action'	=> route('admin.settings.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> 'list',
		];

		return view('admin/setting/edit', $data);
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

			if(Auth::user()->can('view-any', Setting::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.settings.index', [
					'goto_item'		=> \Str::slug($store['stt']->key),
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Setting  $stt
	 * @return \Illuminate\Http\Response
	 */
	public function show(Setting $stt)
	{
		$data = [
			'stt'	=> $stt,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/setting/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Setting  $stt
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Setting $stt)
	{
		//
		$back_url = null;

		if(Auth::user()->can('view', $stt)) {
			$back_url = route('admin.settings.show', ['stt' => $stt->key]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', Setting::class)) {
			$back_url = route('admin.settings.index', ['goto_item' => \Str::slug($stt->key)]);
		}

		$data = [
			'stt'		=> $stt,
			'is_edit'	=> true,
			'action'	=> route('admin.settings.update', ['stt' => $stt->key]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/setting/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Setting  $stt
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Setting $stt)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $stt);

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
			if($backto == 'list' && Auth::user()->can('view-any', Setting::class)) {
				return redirect()->route('admin.settings.index', ['goto_item' => \Str::slug($stt->key), 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $stt)) {
				return redirect()->route('admin.settings.show', ['stt' => $stt->key]);
			}

			return redirect()->back();
		}
	}

	protected function _store($request, $stt = NULL) {

		$is_edit = $stt instanceof Setting;
		if(!$is_edit) {
			$stt = new Setting;
		}

		$user = $request->user();
		$user_id = $user->id;

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'key'			=> [
				'required',
				'max:200',
				new NoSpaces,
				Rule::unique(Setting::class, 'key')->ignore($stt),
			],
			'value'			=> ['nullable', 'string', 'max:500'],
			'description'	=> ['nullable', 'string', 'max:1000'],
		];

		$field_names = [
			'key'		=> __('admin/settings.fields.key'),
			'value'		=> __('admin/settings.fields.value'),
		];

		$validData = $request->validate($rules, [], $field_names);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			$stt->key			= $request->input('key');
			$stt->value			= $request->input('value');
			$stt->description	= $request->input('description');

			$result = $stt->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'stt');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Setting  $stt
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Setting $stt)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $stt->delete();
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

		$redirect = null;
		$backto = request()->query('backto');
		if(Auth::user()->can('view-any', Setting::class)) {
			$redirect = route('admin.settings.index');
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
