<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\Prodi;
use App\User;
use App\Models\Role;
use App\Models\Ability;
use App\Models\UserBlock;

use App\DataManagers\UserManager;

use App\Rules\ModelExists;

use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class UserController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(User::class, 'user');
	}

	public static function scopeListQuery($query, &$view_mode, $user = null) {
		if(!$user)
			$user = Auth::user();

		// Scope filters
		$view_mode = UserManager::userViewMode($user);
		$query->where(function($query) use($user, &$view_mode) {
			if($view_mode == 'all') {
				// No scope filter, enable all
				$query->whereRaw('1');
			} elseif($view_mode == 'prodi') {
				// Only ones in the same prodi
				$query->whereHas('prodi', function($query) use($user) {
					$query->where('id', $user->prodi_id);
					$query->whereNotNull('id');
				});
			} else {
				// None
			}
		});

		if($view_mode == 'none') {
			$query->whereRaw('0 = 1');
		}
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//

		$filters = get_filters(['keyword', 'type', 'prodi_id', 'sort_by'], [
			'sort_by'	=> 'name',
			'type'		=> 'user',
			'prodi_id'	=> 'all',
		]);
		$opt_filters = optional($filters);
		$filter_count = 0;

		$query = User::withCount('apps')->withoutTrashed();
		$query->with('roles');

		$view_mode = '';
		static::scopeListQuery($query, $view_mode);

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('email', 'like', $like);
			});
			$filter_count++;
		}

		if($view_mode == 'all') {
			switch($opt_filters['type']) {
				case 'system':
					$query->system();
					$filter_count++;
					break;
				case 'all':
					// No filters
					$filter_count++;
					break;
				case 'user':
				default:
					// Default
					$query->regular();
					break;
			}
			if($opt_filters['prodi_id'] && $opt_filters['prodi_id'] != 'all') {
				$query->where('prodi_id', $opt_filters['prodi_id']);
				$filter_count++;
			}
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

		// $query->orderByRaw('(entity = ?) asc', ['system']);
		$query->orderBy('entity', 'asc');
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
		$data['total']			= User::withoutTrashed()->regular()->count();
		$data['filters']		= $opt_filters;
		$data['filter_count']	= $filter_count;
		$data['goto_flash']		= $goto_flash;
		$data['delete_params']	= array_merge($filters, ['page' => $page]);
		$data['show_type_col']	= in_array($opt_filters['type'], ['all', 'system']);
		$data['prodis']			= Prodi::all();
		$data['view_mode']		= $view_mode;

		return view('admin/user/index', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// Get view mode
		$view_mode = UserManager::userViewMode();

		if($view_mode == 'all') {
			$prodis = Prodi::all();
		} else {
			$prodis = optional($user->prodi)->name;
		}

		//
		$back_url = null;
		if(Auth::user()->can('view-any', User::class)) {
			$back_url = route('admin.users.index');
		}

		$data = [
			// 'model'		=> new User,
			'model'		=> optional(),
			'prodis'	=> $prodis,
			'allow_role'	=> true,
			'roles'		=> UserManager::getLowerRoles(true),
			'is_edit'	=> false,
			'view_mode'	=> $view_mode,
			'action'	=> route('admin.users.store'),
			'method'	=> 'POST',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> 'list',
		];

		return view('admin/user/edit', $data);
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

			if(Auth::user()->can('view-any', User::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.users.index', [
					'goto_item'		=> $store['user']->id,
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function show(User $user)
	{
		$user->loadCount('apps');

		$roles_abilities = elocollect();
		$user->load([
			'abilities' => function($query) {
				$query->defaultOrder();
			},
			'roles' => function($query) {
				$query->defaultOrder();
			},
			'roles.abilities' => function($query) use(&$roles_abilities) {
				$query->defaultOrder();
				$roles_abilities = $query->get();
			},
		]);
		$user->roles_abilities = $roles_abilities;

		$data = [
			'user'	=> $user,
			'ajax'	=> request()->ajax(),
		];

		return view('admin/user/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function edit(User $user)
	{
		// Get view mode
		$view_mode = UserManager::userViewMode();

		if($view_mode == 'all') {
			$prodis = Prodi::all();
		} else {
			$prodis = optional($user->prodi)->name;
		}

		//
		$back_url = null;

		if(Auth::user()->can('view', $user)) {
			$back_url = route('admin.users.show', ['user' => $user->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', User::class)) {
			$back_url = route('admin.users.index', ['goto_item' => $user->id]);
		}

		$user->load(['roles']);
		$user->roles_ids = $user->roles->modelKeys();

		$allow_role = $user->is_me || Auth::user()->can('manipulate-account', [$user, true]);
		$roles = UserManager::getLowerRoles(true);

		$data = [
			'model'		=> $user,
			'prodis'	=> $prodis,
			'allow_role'	=> $allow_role,
			'roles'		=> $roles,
			'is_edit'	=> true,
			'view_mode'	=> $view_mode,
			'action'	=> route('admin.users.update', ['user' => $user->id]),
			'method'	=> 'PATCH',
			'user'		=> Auth::user(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/user/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $user);

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
			if($backto == 'list' && Auth::user()->can('view-any', User::class)) {
				return redirect()->route('admin.users.index', ['goto_item' => $user->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $user)) {
				return redirect()->route('admin.users.show', ['user' => $user->id]);
			}

			return redirect()->back();
		}
	}

	protected function _store($request, $user = NULL) {

		$is_edit = $user instanceof User;
		if(!$is_edit) {
			$user = new User;
		}

		$cuser = $request->user();
		$cuser_id = $cuser->id;

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'name'			=> ['required', 'max:100'],
			'email'			=> [
				'required',
				'email',
				'max:200',
				Rule::unique(User::class, 'email')->ignore($user),
			],
			'prodi_id'		=> ['nullable', new ModelExists(Prodi::class)],
			// 'password'		=> ['required', 'confirmed'],
			'password'		=> [Rule::requiredIf(!$is_edit), 'confirmed'],
			// 'password_confirmation'	=> 'required',
			'roles'			=> ['nullable', 'array'],
			'roles.*.id'	=> ['nullable', new ModelExists(Role::class)],
		];

		$validData = $request->validate($rules);

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			$user->name			= $request->input('name');
			$user->email		= $request->input('email');
			$user->prodi_id		= $request->input('prodi_id');
			$user->password		= Hash::make($request->input('password'));

			$result = $user->save();

			// Roles
			if(!$is_edit || $cuser->can('manipulate-account', $user)) {
				$input_role_ids = $request->input('roles', []);
				$role_ids = [];
				foreach($input_role_ids as $irole) {
					if(!isset($irole['check'])) continue;
					$role_ids[] = $irole['id'];
				}
				Bouncer::sync($user)->roles($role_ids);
			}

			// Refresh user authorization cache
			Bouncer::refreshFor($user);
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		return compact('result', 'messages', 'user');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, User $user)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $user->delete();
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


	public function resetPassword(Request $request, User $user) {
		$this->authorize('reset-password', $user);

		// Reset a user's password to a random string

		$new_pass = random_alpha(1).random_string(7);

		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$user->password = Hash::make($new_pass);
			$result = $user->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if($result) {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/users.messages.reset_password_successful'),
				'type'		=> 'success'
			]);
			$request->session()->flash('user_'.$user->id.'_new_pass', $new_pass);

			$backto = $request->input('backto');
			return redirect()->route('admin.users.reset_password', ['user' => $user->id, 'backto' => $backto]);
		} else {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/users.messages.reset_password_failed'),
				'type'		=> 'danger'
			]);

			return redirect()->route('admin.users.index', ['goto_item' => $user->id]);
		}
	}

	public function afterResetPassword(Request $request, User $user) {
		$this->authorize('reset-password', $user);

		// Show a transient page about the new password
		$new_pass_key = 'user_'.$user->id.'_new_pass';
		if(!session()->has($new_pass_key)) {
			// Redirect or just show 404?
			// return abort(404);
			return redirect()->route('admin.users.index', ['goto_item' => $user->id]);
		}

		$new_pass = session($new_pass_key);

		$back_url = null;

		if(Auth::user()->can('view', $user)) {
			$back_url = route('admin.users.show', ['user' => $user->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', User::class)) {
			$back_url = route('admin.users.index', ['goto_item' => $user->id, 'goto_flash' => 1]);
		}
		$data = [
			'user'		=> $user,
			'new_pass'	=> $new_pass,
			'back'		=> $back_url,
		];

		// Prevent caching of this page
		// https://stackoverflow.com/a/1907705
		$response = no_cache_headers(
			response()->view('admin/user/after-reset-password', $data)
		);
		return $response;
	}


	/**
	 * Show form to block the user.
	 *
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function blockForm(User $user)
	{
		$this->authorize('block', $user);

		$back_url = null;

		if(Auth::user()->can('view', $user)) {
			$back_url = route('admin.users.show', ['user' => $user->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', User::class)) {
			$back_url = route('admin.users.index', ['goto_item' => $user->id]);
		}

		$data = [
			'model'		=> $user,
			'ajax'		=> request()->ajax(),
			'back'		=> $back_url,
			'backto'	=> $backto,
		];

		return view('admin/user/block-form', $data);
	}

	/**
	 * Block the specified user.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	// POST
	public function block(Request $request, User $user) {
		$this->authorize('block', $user);

		$cuser = $request->user();
		$cuser_id = $cuser->id;

		// Validation rules
		request_replace_nl($request);
		$rules = [
			// 'dummy'			=> ['required'],
			'reason'			=> ['required', 'string', 'min:20', 'max:200'],
		];

		$validData = $request->validate($rules);

		$result = true;
		$messages = [];

		// Begin storing entries
		DB::beginTransaction();
		try {
			$user->is_blocked = true;
			$result = $user->save();

			if($result) {
				$block = new UserBlock;
				$block->user_id = $user->id;
				$block->reason = $request->input('reason');

				$result = $block->save();
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($messages);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/users.messages.block_successful'),
				'type'		=> 'success'
			]);

			$backto = $request->input('backto');
			if($backto == 'list' && Auth::user()->can('view-any', User::class)) {
				return redirect()->route('admin.users.index', ['goto_item' => $user->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $user)) {
				return redirect()->route('admin.users.show', ['user' => $user->id]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Show page about user's past blocks.
	 *
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	public function blockHistory(User $user)
	{
		$this->authorize('view', $user);

		$back_url = null;

		if(Auth::user()->can('view', $user)) {
			$back_url = route('admin.users.show', ['user' => $user->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', User::class)) {
			$back_url = route('admin.users.index', ['goto_item' => $user->id]);
		}

		$active_blocks = $user->blocks;
		$inactive_blocks = $user->inactive_blocks;

		$data = [
			'model'		=> $user,
			'ajax'		=> request()->ajax(),
			'back'		=> $back_url,
			'backto'	=> $backto,
			'blocks_active'		=> $active_blocks,
			'blocks_inactive'	=> $inactive_blocks,
		];

		return view('admin/user/block-history', $data);
	}

	/**
	 * Unblock the specified user.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  User  $user
	 * @return \Illuminate\Http\Response
	 */
	// POST
	public function unblock(Request $request, User $user) {
		$this->authorize('unblock', $user);

		$cuser = $request->user();
		$cuser_id = $cuser->id;

		$result = true;
		$messages = [];

		// Begin storing entries
		DB::beginTransaction();
		try {
			$user->is_blocked = false;
			$result = $user->save();

			if($result) {
				$result = $user->blocks->every(function($item) {
					return $item->delete();
				});
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($messages);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/users.messages.unblock_successful'),
				'type'		=> 'success'
			]);

			$backto = $request->input('backto');
			if($backto == 'list' && Auth::user()->can('view-any', User::class)) {
				return redirect()->route('admin.users.index', ['goto_item' => $user->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $user)) {
				return redirect()->route('admin.users.show', ['user' => $user->id]);
			}

			return redirect()->back();
		}
	}


	public function lookup(Request $request, $keyword = '') {
		// No auth needed i guess? Since lookup is usually used in other model's forms

		$data = [];
		$query = User::query()->regular();

		$ids = $request->query('ids');
		// TODO: scope by prodi (if no bypass)
		if($ids) {
			$ids = explode(',', $ids);
			$query->whereKey($ids);
			$query->orderBy('name');
			$query->orderBy('email');

			$count_all = (clone $query)->count();
			$offset = 0;
		} else {
			if($keyword == '') {
				$keyword_keys = ['keyword', 'q', 'term'];
				foreach($keyword_keys as $k) {
					$keyword = $keyword ?: $request->query($k, '');
					if($keyword) break;
				}
			}

			if($keyword != '') {
				$query->where(function($query) use ($keyword) {
					$param = '%'. escape_mysql_like_str($keyword) .'%';
					$query->where('name', 'like', $param)
						->orWhere('email', 'like', $param);
				});
			}
			$query->orderBy('name');
			$query->orderBy('email');

			$count_all = (clone $query)->count();

			$per_page = 10;
			$offset = 0;
			if($request->query('_term') == 'query_append' && ($page = $request->query('page')) ) {
				// Select2 pagination
				$offset = ($page - 1) * $per_page;
			}
			$query->limit($per_page);
			$query->offset($offset);
		}

		$result = $query->get();
		// $result = sort_strpos($result, $keyword, ['name', 'email'])->values();
		$result->transform(function($item, $key) {
			$item->text = sprintf('%s (%s)', $item->name, $item->email ?: ' - ');
			$item = collect($item->toArray());
			$item = $item->only(['id', 'name', 'email', 'text']);
			return $item->all();
		});

		$data = $result->all();

		return response()->json([
			'success'	=> true,
			'data'		=> $data,
			'total'		=> $count_all,
			'more'		=> ($offset + count($data)) < $count_all,
		]);
	}
}
