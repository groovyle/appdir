<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\Prodi;
use App\User;
use App\Models\Role;
use App\Models\Ability;

use App\Rules\ModelExists;

use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

use Silber\Bouncer\Database\Titles\RoleTitle;

class RoleController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(Role::class, 'role');
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

		$query = Role::withCount('users');

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('title', 'like', $like);
			});
			$filter_count++;
		}

		$query->orderBy('title');
		$query->orderBy('name');

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
		$data['total']			= Role::count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);

		return view('admin/role/index', $data);
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
		if(Auth::user()->can('view-any', Role::class)) {
			$back_url = route('admin.roles.index');
		}

		$data = [
			// 'role'		=> new Role,
			'role'		=> optional(),
			'is_edit'	=> false,
			'abilities'	=> Ability::defaultOrder()->get(),
			'action'	=> route('admin.roles.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> 'list',
		];

		return view('admin/role/edit', $data);
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

			if(Auth::user()->can('view-any', Role::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.roles.index', [
					'goto_item'		=> $store['role']->id,
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  Role  $role
	 * @return \Illuminate\Http\Response
	 */
	public function show(Role $role)
	{
		$role->load([
			'abilities' => function($query) {
				$query->defaultOrder(true, true);
			},
			'users' => function($query) {
				$query->orderBy('name');
				$query->orderBy('email');
			}
		]);

		$data = [
			'role'	=> $role,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/role/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Role  $role
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Role $role)
	{
		//
		$back_url = null;

		if(Auth::user()->can('view', $role)) {
			$back_url = route('admin.roles.show', ['role' => $role->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', Prodi::class)) {
			$back_url = route('admin.roles.index', ['goto_item' => $role->id]);
		}

		$role->load(['abilities', 'users']);
		$role->abilities_ids = $role->abilities->modelKeys();
		$role->abilities_modes = $role->abilities->mapWithKeys(function($item) {
			return [$item->id => $item->pivot->forbidden ? 'forbid' : 'allow'];
		})->all();
		$role->users_ids = $role->users->modelKeys();

		$data = [
			'role'		=> $role,
			'is_edit'	=> true,
			'abilities'	=> Ability::defaultOrder()->get(),
			'action'	=> route('admin.roles.update', ['role' => $role->id]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/role/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  Role  $role
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, Role $role)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $role);

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
			if($backto == 'list' && Auth::user()->can('view-any', Role::class)) {
				return redirect()->route('admin.roles.index', ['goto_item' => $role->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $role)) {
				return redirect()->route('admin.roles.show', ['role' => $role->id]);
			}

			return redirect()->back();
		}
	}

	protected function _store($request, $role = NULL) {

		$is_edit = $role instanceof Role;
		if(!$is_edit) {
			$role = new Role;
		}

		$user = $request->user();
		$user_id = $user->id;

		// Validation rules
		request_replace_nl($request);
		// dd($request->all());
		$rules = [
			// 'dummy'			=> ['required'],
			'name'			=> [
				Rule::requiredIf(!$is_edit),
				'max:100',
				// Rule::unique(Role::class, 'name')->ignore($role),
			],
			'title'			=> ['nullable', 'string', 'max:200'],
			'abilities'		=> ['nullable', 'array'],
			'abilities.*.id'	=> ['required', new ModelExists(Ability::class)],
			'abilities.*.mode'	=> ['nullable', Rule::in(['allow', 'forbid']) ],
			'abilities.*.check'	=> ['nullable'],
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
			if(!$is_edit) {
				$role->name	= $request->input('name');
			}

			$role->title	= $request->input('title');
			if(is_null($role->title)) {
				$role->title = RoleTitle::from($role)->toString();
			}

			$result = $role->save();


			// Abilities
			$input_abilities = $request->input('abilities', []);
			$abilities_allowed = [];
			$abilities_forbidden = [];
			foreach($input_abilities as $iabl) {
				if(!isset($iabl['check'])) continue;
				$forbid = ($iabl['mode'] ?? null) == 'forbid';
				if($forbid) {
					$abilities_forbidden[] = $iabl['id'];
				} else {
					$abilities_allowed[] = $iabl['id'];
				}
			}
			Bouncer::sync($role)->abilities($abilities_allowed);
			Bouncer::sync($role)->forbiddenAbilities($abilities_forbidden);


			// Users
			$input_user_ids = $request->input('users', []);
			$role->syncUsers($input_user_ids);

			Bouncer::refresh();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'role');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Role  $role
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, Role $role)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $role->delete();
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
		if(Auth::user()->can('view-any', Role::class)) {
			$redirect = route('admin.roles.index');
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
