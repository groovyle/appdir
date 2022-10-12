<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\Prodi;
use App\User;
use App\Models\Role;
use App\Models\Ability;

use Bouncer;

use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

use Silber\Bouncer\Database\Titles\AbilityTitle;

class AbilityController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(Ability::class, 'abl');
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

		$query = Ability::withCount('users');

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('title', 'like', $like);
				$query->orWhere('entity_type', 'like', $like);
			});
			$filter_count++;
		}

		$query->defaultOrder();
		// $query->orderBy('title');
		// $query->orderBy('name');

		$per_page = 10;
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
		$data['total']			= Ability::count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/ability/index', $data);
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
		if(Auth::user()->can('view-any', Prodi::class)) {
			$back_url = route('admin.abilities.index');
		}

		$data = [
			// 'abl'		=> new Ability,
			'abl'		=> optional(),
			'is_edit'	=> false,
			'roles'		=> Role::defaultOrder()->get(),
			'action'	=> route('admin.abilities.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> 'list',
		];

		return view('admin/ability/edit', $data);
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

			if(Auth::user()->can('view-any', Ability::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.abilities.index', [
					'goto_item'		=> $store['abl']->id,
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Role  $abl
	 * @return \Illuminate\Http\Response
	 */
	public function show(Ability $abl)
	{
		$abl->load([
			'roles' => function($query) {
				$query->defaultOrder(true);
			},
			'users' => function($query) {
				$query->orderBy('name');
				$query->orderBy('email');
			}
		]);

		$data = [
			'abl'	=> $abl,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/ability/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Role  $abl
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Ability $abl)
	{
		//
		$abl->load(['roles', 'users']);
		$abl->roles_ids = $abl->roles->modelKeys();
		$abl->roles_modes = $abl->roles->mapWithKeys(function($item) {
			return [$item->id => $item->pivot->forbidden ? 'forbid' : 'allow'];
		})->all();
		$abl->users_ids = $abl->users->modelKeys();

		$back_url = null;

		if(Auth::user()->can('view', $abl)) {
			$back_url = route('admin.abilities.show', ['abl' => $abl->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', Ability::class)) {
			$back_url = route('admin.abilities.index', ['goto_item' => $abl->id]);
		}

		$data = [
			'abl'		=> $abl,
			'is_edit'	=> true,
			'roles'		=> Role::defaultOrder()->get(),
			'action'	=> route('admin.abilities.update', ['abl' => $abl->id]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/ability/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Role  $abl
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Ability $abl)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $abl);

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
			if($backto == 'list' && Auth::user()->can('view-any', Ability::class)) {
				return redirect()->route('admin.abilities.index', ['goto_item' => $abl->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $abl)) {
				return redirect()->route('admin.abilities.show', ['abl' => $abl->id]);
			}

			return redirect()->back();
		}
	}

	protected function _store($request, $abl = NULL) {

		$is_edit = $abl instanceof Ability;
		if(!$is_edit) {
			$abl = new Ability;
		}

		$user = $request->user();
		$user_id = $user->id;

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'name'			=> [
				'required',
				// Rule::requiredIf(!$is_edit),
				'max:100',
				// Rule::unique(Ability::class, 'name')->ignore($abl),
			],
			'title'			=> ['nullable', 'string', 'max:200'],
			'entity_type'	=> ['nullable', 'string', 'max:100'],
			'entity_id'		=> ['nullable', 'string', 'max:100'],
			'only_owned'	=> ['nullable'],
			'roles'			=> ['nullable', 'array'],
			'roles.*.id'	=> ['nullable', new ModelExists(Role::class)],
			'roles.*.mode'	=> ['nullable', Rule::in(['allow', 'forbid']) ],
			'roles.*.check'	=> ['nullable'],
			'users'			=> ['nullable', 'array'],
			'users.*'		=> [new ModelExists(User::class, null, null, function($query) {
				$query->regular();
			})],
		];

		$validData = $request->validate($rules);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			/*if(!$is_edit) {
				$abl->name = $request->input('name');
			}*/
			$abl->name = $request->input('name');
			$abl->entity_type = $request->input('entity_type');
			$abl->entity_id = $request->input('entity_id');
			$abl->only_owned = $request->input('only_owned') == '1';

			$abl->title	= $request->input('title');
			if(is_null($abl->title)) {
				$abl->title = AbilityTitle::from($abl)->toString();
			}

			$result = $abl->save();

			// Roles
			$input_role_ids = $request->input('roles', []);
			$role_ids_used = [];
			$role_ids_unused = [];
			foreach($input_role_ids as $irole) {
				if(!isset($irole['check'])) {
					$role_ids_unused[] = $irole['id'];
				} else {
					$role_ids_used[$irole['id']] = ['forbidden' => ($irole['mode'] ?? null) == 'forbid' ? 1 : 0];
				}
			}
			// Can't do the following because it discards the forbidden attribute
			// $abl->roles()->sync($role_ids);

			// Manually do each role
			$current_roles = $abl->roles;
			$roles_unused = Role::findMany($role_ids_unused);
			foreach($current_roles->intersect($roles_unused) as $r) {
				Bouncer::disallow($r)->to($abl);
				Bouncer::unforbid($r)->to($abl);
			}
			$roles_used = Role::findMany(array_keys($role_ids_used));
			foreach($roles_used as $r) {
				Bouncer::disallow($r)->to($abl);
				Bouncer::unforbid($r)->to($abl);
				if($role_ids_used[$r->id]['forbidden']) {
					Bouncer::forbid($r)->to($abl);
				} else {
					Bouncer::allow($r)->to($abl);
				}
			}


			// Users
			$user_ids = $request->input('users', []);
			$abl->syncUsers($user_ids);
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'abl');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Role  $abl
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Ability $abl)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $abl->delete();
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
		if(Auth::user()->can('view-any', Ability::class)) {
			$redirect = route('admin.abilities.index');
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
