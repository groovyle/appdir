<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppChangelog;
use App\Models\AppTag;
use App\Models\AppLogo;
use App\Models\AppVisualMedia;
use App\Models\AppType;
use App\Models\AppVerification;
use App\Models\LogActions;
use App\Models\VerificationStatus;
use App\Models\SystemUsers\Automator;
use App\Settings;
use App\User;

use App\DataManagers\AppManager;

use App\Rules\AppDirectory;
use App\Rules\AppUrl;
use App\Rules\FQDN;
use App\Rules\ModelExists;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\ViewErrorBag;
use RahulHaque\Filepond\Facades\Filepond;
use Gumlet\ImageResize;

class AppController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
		$this->authorizeResource(App::class, 'app');
	}

	/**
	 * Get the map of resource methods to ability names.
	 *
	 * @return array
	 */
	protected function resourceAbilityMap()
	{
		return [
			'index'		=> 'view-any',
			'show'		=> 'view',
			'create'	=> 'create',
			'store'		=> 'create',
			'edit'		=> 'update',
			'update'	=> 'update',
			'destroy'	=> 'delete',
			'visuals'	=> 'update',
			'updateVisuals'	=> 'update',
			'verifications'	=> 'view-verifications',
			'changes'		=> 'view-changelog',
			'reviewChanges'		=> 'update',
			'publishChanges'	=> 'update',
			'afterPublishChanges'	=> 'update',
		];
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		//
		$data = [];
		$user = Auth::user();
		// $apps = Auth::user()->apps()->get();

		$filters = get_filters(['keyword', 'status', 'published', 'categories', 'tags', 'owned']);
		$opt_filters = optional($filters);
		$filter_count = 0;

		$categories = array_filter(explode(',', $opt_filters['categories']));
		$tags = array_filter(explode(',', $opt_filters['tags']));

		$query = (new App)->newQueryWithoutScopes();
		$query->with([
			'last_verification.status',
			'last_changes',
			'categories',
			'tags',
		]);

		$query->from('apps as a');
		$query->leftJoin('app_changelogs as cv', 'a.version_id', '=', 'cv.id');
		$query->leftJoin('app_changelogs as cl', function($query) {
			$query->on('a.id', '=', 'cl.app_id');
			$query->whereIn('cl.status', [AppChangelog::STATUS_PENDING, AppChangelog::STATUS_APPROVED]);
			$query->whereRaw('if(cv.id is not null, cl.created_at >= cv.created_at and cl.id >= cv.id, 1)');
		});
		$query->leftJoin('app_categories as acat', function($query) use($categories) {
			$query->on('a.id', '=', 'acat.app_id');
			if($categories) {
				$query->whereIn('acat.category_id', $categories);
			}
		});
		$query->leftJoin('app_tags as atag', function($query) use($tags) {
			$query->on('a.id', '=', 'atag.app_id');
			if($tags) {
				$query->whereIn('atag.tag', $tags);
			}
		});
		$query->leftJoin('users as o', 'a.owner_id', '=', 'o.id');
		$query->leftJoin('ref_prodi as prodi', 'o.prodi_id', '=', 'prodi.id');

		// Do not include trashed/deleted items
		$query->whereNull('a.deleted_at');

		$total_all = $query->count(DB::raw('distinct a.id'));

		// Role/ability scoping filter
		$view_mode = 'owned';
		AppManager::scopeListQuery($query, $view_mode);
		$total_scoped = $query->count(DB::raw('distinct a.id'));


		$query->groupBy('a.id');
		$query->orderBy('a.is_reported', 'desc'); // bring reported apps into attention
		$query->orderBy('a.name', 'asc');
		$query->orderBy('a.updated_at', 'desc');
		$query->orderBy('a.id', 'desc');
		$query->select('a.*');
		$query->selectRaw('(count(cl.id) > 0) as has_floating');
		$query->selectRaw('(count(if(cl.status = ?, cl.id, null)) > 0) as has_pending', [AppChangelog::STATUS_PENDING]);
		$query->selectRaw('(count(if(cl.status = ?, cl.id, null)) > 0) as has_approved', [AppChangelog::STATUS_APPROVED]);

		if($keyword = trim($opt_filters['keyword'])) {
			$str = escape_mysql_like_str($keyword);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('a.name', 'like', $like);
				$query->orWhere('a.short_name', 'like', $like);
				$query->orWhere('a.description', 'like', $like);
			});
			$filter_count++;
		}

		if(count($categories) > 0) {
			// operator OR (match any)
			// $query->whereIn('acat.category_id', $categories);

			// operator AND (match all)
			$query->havingRaw('count(distinct acat.category_id) = ?', [count($categories)]);
			$filter_count++;
		}
		if(count($tags) > 0) {
			// operator OR (match any)
			// $query->whereIn('atag.tag', $tags);

			// operator AND (match all)
			$query->havingRaw('count(distinct atag.tag) = ?', [count($tags)]);
			$filter_count++;
		}

		switch($opt_filters['status']) {
			case 'unverified':
				$query->whereNotNull('cl.id');
				$filter_count++;
				break;
			case 'verified':
				$query->whereNull('cl.id');
				$filter_count++;
				break;
		}

		switch($opt_filters['published']) {
			case 'yes':
				$query->where('is_published', 1);
				$filter_count++;
				break;
			case 'no':
				$query->where('is_published', 0);
				$filter_count++;
				break;
		}

		switch($opt_filters['owned']) {
			case 'mine':
				$query->where('a.owner_id', '=', $user->id);
				$filter_count++;
				break;
			case 'others':
				$query->where('a.owner_id', '!=', $user->id);
				$filter_count++;
				break;
		}

		$per_page = 10;
		$page = request()->input('page', 1);

		$items = $query->paginate($per_page);
		$items->appends($filters);

		// Redirect if over page. This can happen when e.g the last item in the
		// last page gets deleted. Redirect to last available page.
		if($items->total() > 0 && $page > $items->lastPage()) {
			return redirect()->to( $items->url($items->lastPage()) );
		}

		$data['view_mode'] = $view_mode;
		$data['prodi'] = optional($user->prodi);
		$data['items'] = $items;
		$data['total_all'] = $total_all;
		$data['total_scoped'] = $total_scoped;
		$data['filters'] = $opt_filters;
		$data['filter_count'] = $filter_count;
		$data['categories'] = AppCategory::all();
		$data['tags'] = AppTag::all();

		return view('admin/app/index', $data);
	}

	protected function _getFormPreps() {
		$data = [];
		$data['user'] = Auth::user();
		$data['types'] = AppType::get();
		$data['categories'] = AppCategory::get();
		$data['tags'] = AppTag::get()->pluck('name');

		return $data;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		$data = $this->_getFormPreps();

		//
		$back_url = null;
		if(Auth::user()->can('view-any', App::class)) {
			$back_url = route('admin.apps.index');
		}

		$app = new App;

		$data['app'] = $app;
		$data['is_edit'] = FALSE;
		$data['pending_add'] = settings('app.creation_needs_verification', false);

		$data['action'] = route('admin.apps.store');
		$data['method'] = 'POST';
		$data['back'] = $back_url;

		return view('admin/app/edit', $data);
	}

	protected function _store($request, $app = NULL) {

		$is_edit = $app instanceof App;
		if(!$is_edit) {
			$app = new App;
		} elseif($app->has_floating_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app, false, false);
		}

		$user = $request->user();
		$user_id = $user->id;

		$logo_input_hash = $request->input('app_logo');
		$delete_files = array();
		$uploaded_files = array();

		$verf_add = settings('app.creation_needs_verification', false);
		$verf_edit = settings('app.modification_needs_verification', false);


		// Validation rules
		request_replace_nl($request);
		$rules = [
			'app_name'			=> ['required', 'min:10', 'max:100'],
			'app_short_name'	=> ['nullable', 'min:3', 'max:20'],
			'app_description'	=> ['nullable', 'string'],
			'app_url'			=> ['nullable', 'string', 'url', new AppUrl],
			// 'app_logo'			=> ['file', 'image', 'max:2048'], // NOTE: using filepond validation instead
			'categories'		=> ['required', 'array'],
			'categories.*'		=> [/*'required', */'integer', new ModelExists(AppCategory::class)],
			'tags'				=> [/*'required', */'array'],
			'tags.*'			=> [/*'required', */'string', 'alpha_dash'],
			'visuals'			=> ['array', 'max:'.ini_max_file_uploads()],
			'visuals.*'			=> ['file', 'image', 'max:2048'],
		];

		// Settings-based rules
		if($desc_limit = settings('app.description_limit'))
			$rules['app_description'][] = 'max:'.$desc_limit;

		list($cat_min, $cat_max) = settings('app.categories.range');
		if($cat_min) $rules['categories'][] = 'min:'.$cat_min;
		if($cat_max) $rules['categories'][] = 'max:'.$cat_max;

		list($tags_min, $tags_max) = settings('app.tags.range');
		if($tags_min) $rules['tags'][] = 'min:'.$tags_min;
		if($tags_max) $rules['tags'][] = 'max:'.$tags_max;

		if($vis_max_size = settings('app.visuals.max_size'))
			$rules['visuals.*'][] = 'max:'.$vis_max_size;

		// END Settings-based rules

		$validData = $request->validate($rules);

		// Validate files
		$validFiles = Filepond::field($logo_input_hash)->validate([
			'app_logo'	=> ['nullable', 'file', 'image', 'max:2048'],
		]);

		AppManager::prepareForVersionDiff($app);

		if(!$is_edit) {
			$app->owner_id		= $user_id;
		}

		$app->name			= $request->app_name;
		$app->short_name	= $request->has('app_has_short_name') ? $request->app_short_name : null;
		$app->description	= $request->app_description;

		if($request->app_url) {
			// For URLs that don't start with a scheme, automatically add one.
			$url = url_auto_scheme($request->app_url);
			// $app->url			= trim($url, '/').'/';
		} else {
			$url = null;
		}
		$app->url			= $url;

		if(!$is_edit) {
			$app->owner_id		= $user_id;

			// NOTE: slug is only generated during creation to prevent any duplicates
			// while doing pending changes in case of edits
			$inc = 1;
			do {
				$app->slug = Str::slug($app->public_name . ($inc > 1 ? '-'.$inc : ''));
				$query = App::where('slug', $app->slug);
				if($is_edit) {
					$query->where('id', '<>', $app->id);
				}
				$exists = $query->exists();
				$inc++;
			} while($exists);
		}

		$result = TRUE;
		$messages = [];

		// Begin storing entries
		try {
			// Tags
			// Insert the tags first if they don't exist yet
			$tags = $request->input('tags', []);
			$categories = $request->input('categories', []);
			foreach($tags as $tag) {
				AppTag::firstOrCreate(
					['name' => $tag],
					[
						'slug'			=> Str::slug($tag),
						'creator_id'	=> $user_id
					]
				);
			}

			if(!$is_edit) {
				// The app
				// Save the app regardless of verification settings, because
				// we need the entity to exist
				$result = $result && $app->save();
				$app->categories()->attach($categories);
				$app->tags()->attach($tags);
			} else {
				// NOTE: if verification is used, attach/detach relationships later

				// Categories
				// Detach all then attach
				// TODO: why not use sync(), is there a specific reason?
				/*$app->categories()->detach();
				$app->categories()->attach($categories);*/
				// $app->categories()->sync($categories);

				// Tags
				// Detach all then attach
				/*$app->tags()->detach();
				$app->tags()->attach($tags);*/
				// $app->tags()->sync($tags);
			}

			$rel_categories = AppCategory::findMany($categories);
			$app->setRelation('categories', $rel_categories);

			$rel_tags = AppTag::findMany($tags);
			$app->setRelation('tags', $rel_tags);

			// Make sure storage dir exists
			$storage = Storage::disk('public');
			$storage_rel_path = 'apps/'.$app->id.'/';
			$storage_path = $storage->path($storage_rel_path);
			if(!$storage->has($storage_rel_path)) {
				$storage->createDir($storage_rel_path);
			}

			// Logo
			// New logo and/or delete previous logo
			$new_logo = $request->has('app_logo');
			$delete_logo = $request->input('app_logo_delete', 0) == 1;
			if($is_edit && ($new_logo || $delete_logo) && $app->logo) {
				// Delete current logo
				// $app->logo->delete();

				// NOTE: respect verf settings, don't delete here
				$app->setRelation('logo', null);
			}
			// Process logo
			// $logo_file = $request->file('app_logo');
			$logo_result = true;
			$logo_file = Filepond::field($logo_input_hash)->getFile();
			if($new_logo && $logo_file) {
				// Try to process the image anyway to potentially remove malicious codes
				// inside the file.
				$logo_resize = settings('app.logo_resize');
				list($logo_maxw, $logo_maxh) = $logo_resize;
				$logo_resize = $logo_maxw && $logo_maxh;

				// Random filename
				$filename = 'logo-'.$logo_file->hashName();
				$finfo = pathinfo($filename);
				$extension = strtolower($finfo['extension']);
				$barename = $finfo['filename'];
				if(!in_array($extension, ['jpg', 'jpeg'])) {
					// Convert to JPG if it's non standard
					$extension = 'jpg';
					$logo_fname = $barename.'.'.$extension;
				} else {
					$logo_fname = $filename;
				}

				try {
					$img = new ImageResize($logo_file->getPathname());
					$fpath = $storage_path.$logo_fname;
					// Check rescaling criteria
					if(!$logo_resize
						|| ($img->getSourceWidth() <= $logo_maxw
							&& $img->getSourceHeight() <= $logo_maxh)
					) {
						// Store original
						$img->scale(100)->save($fpath, IMAGETYPE_JPEG);
					} else {
						// Scale down
						$img->resizeToBestFit($logo_maxw, $logo_maxh)->save($fpath, IMAGETYPE_JPEG);
					}
					$uploaded_files[] = $fpath;

					$logo_upl = new File($fpath);
				} catch(\Exception $e) {
					$logo_result = false;
					$messages[] = 'Gagal memproses logo unggahan: '. $logo_file->getClientOriginalName(); // TODO: fix message
				}

				if($result) {
					// Gather image meta, store as json string
					$meta = [
						'dimensions'	=> $img->getDestWidth() .'x'. $img->getDestHeight(),
						'size'			=> bytes_to_text($logo_upl->getSize()),
						'extension'		=> strtoupper($extension),
					];

					// Save the logo model
					$logo = new AppLogo;
					$logo->app_id = $app->id;
					$logo->media_name = $logo_fname;
					$logo->media_path = $storage_rel_path.$logo_fname;
					$logo->meta = $meta;
					if($is_edit) {
						// NOTE: Respect verf settings if edit, undelete later
						$logo->deleted_at = $logo->freshTimestampString();
					}

					$logo_result = $logo_result && $logo->save();
					$app->setRelation('logo', $logo);
				}
			}
			$result = $result && $logo_result;


			// Generate app diff
			if($result) {
				$changes = AppManager::diffSave($app);
				$result = $changes['status'];
			}

			if($is_edit && $result) {
				// Save edit only after diffing
				// TODO: what about staging?
				// $result = $result && $app->save();
			}

			// Delete temp files
			if($result && $logo_result) {
				Filepond::field($logo_input_hash)->delete();
			}

			// TODO: check config whether verification is used at all
		} catch(\Illuminate\Database\QueryException $e) {
			$result = FALSE;
			$messages[] = $e->getMessage();
		}

		if(!$result) {
			// Delete the already uploaded files
			foreach($uploaded_files as $path) {
				Storage::disk('public')->delete($path);
			}
		} else {
			// Delete the designated files
			foreach($delete_files as $path) {
				Storage::disk('public')->delete($path);
			}
		}

		return compact('result', 'messages', 'app');
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

		$verf_add = settings('app.creation_needs_verification', false);
		$result = $store['result'];
		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.create_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.create_successful'),
				'type'		=> 'success'
			]);
			if($verf_add) {
				$request->session()->flash('messages', __('admin/apps.messages.create_successful_pending'));
			}

			if(Auth::user()->can('view', $store['app'])) {
				return redirect()->route('admin.apps.show', [
					'app'	=> $store['app']->id,
				]);
			} elseif(Auth::user()->can('view-any', App::class)) {
				// Scroll to the just added item
				return redirect()->route('admin.apps.index', [
					'goto_item'		=> $store['app']->id,
					'goto_flash'	=> 1,
				]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function show(App $app)
	{
		//
		$data = [];
		$data['app'] = $app;

		return view('admin/app/detail', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function edit(App $app)
	{
		$data = $this->_getFormPreps();

		// Show the latest edits if there are any pending changes
		if($app->has_floating_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
			$data['ori'] = $ori;
		}

		$back_url = null;

		if(Auth::user()->can('view', $app)) {
			$back_url = route('admin.apps.show', ['app' => $app->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', App::class)) {
			$back_url = route('admin.apps.index', ['goto_item' => $app->id]);
		}

		$data['app'] = $app;
		$data['is_edit'] = TRUE;
		$data['pending_edits'] = settings('app.modification_needs_verification', false);

		$data['action'] = route('admin.apps.update', ['app' => $app->id]);
		$data['method'] = 'PATCH';
		$data['back'] = $back_url;

		$data['user'] = Auth::user();

		return view('admin/app/edit', $data);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, App $app)
	{
		// Begin storing entries
		DB::beginTransaction();

		$store = $this->_store($request, $app);

		if(is_object($store)) {
			// Presumably a Response object
			return $store;
		}

		$result = $store['result'];
		$verf_edit = settings('app.modification_needs_verification', false);
		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			if( (!$verf_edit && $store['app']->wasChanged())
				|| ($verf_edit && $store['app']->isDirty()) ) {
				// Pass a message
				$request->session()->flash('flash_message', [
					'message'	=> __('admin/apps.messages.update_successful'),
					'type'		=> 'success'
				]);
				if($verf_edit && !$app->is_unverified_new) {
					$request->session()->flash('messages', __('admin/apps.messages.update_successful_pending'));
				}
			}

			$backto = $request->input('backto');
			if($backto == 'list' && Auth::user()->can('view-any', App::class)) {
				return redirect()->route('admin.apps.index', ['goto_item' => $app->id, 'goto_flash' => 1]);
			} elseif(Auth::user()->can('view', $app)) {
				return redirect()->route('admin.apps.show', ['app' => $app->id]);
			}

			return redirect()->back();
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request, App $app)
	{
		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$result = $app->delete();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		$result = false;
		if($result) {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.delete_successful'),
				'type'		=> 'success'
			]);
		} else {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.delete_failed'),
				'type'		=> 'danger'
			]);
		}

		$redirect = null;
		$backto = request()->query('backto');
		if(Auth::user()->can('view-any', App::class)) {
			$redirect = route('admin.apps.index');
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

	public function verifications(App $app)
	{
		$data = [];
		$data['app'] = $app;

		$app->load(['verifications.verifier', 'verifications.status']);

		return view('admin/app/verifications', $data);
	}

	public function changes(App $app)
	{
		$data = [];
		$data['app'] = $app;

		$per_page = 10;
		$page = request()->input('page', 1);
		$go_current = request()->has('current');
		$go_version = request()->input('go_version');
		$go_flash = request()->input('go_flash');
		if(($go_current && $app->version_id) || $go_version) {
			// Find page
			// https://stackoverflow.com/questions/9086719/mysql-paginated-results-find-page-for-specific-result
			// Normally you'd do operator <= but since the order is desc, we use >=
			if($go_version) {
				$target_version = $app->changelogs()->where('version', $go_version)->firstOrNew([]);
				$target_version = $target_version->id;
			} else {
				$target_version = $app->version_id;
			}
			$offset = $app->changelogs()
				->where('id', '>=', $target_version)
				->count()
			;
			if($offset) {
				$page = ceil($offset / $per_page);
				$data['goto_version'] = $target_version;
			}

			$data['goto_flash'] = $go_flash == 1;
		}

		$changelogs = $app->changelogs()->paginate($per_page, ['*'], 'page', $page);
		$data['changelogs'] = $changelogs;
		$data['page'] = $page;
		$data['linked_based_on'] = true;

		return view('admin/app/changes/index', $data);
	}

	public function visuals(App $app)
	{
		$data = [];

		$app->load('visuals');
		// Show the latest edits if there are any pending changes
		if($app->has_floating_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
			$data['ori'] = $ori;
		}

		$back_url = null;

		if(Auth::user()->can('view', $app)) {
			$back_url = route('admin.apps.show', ['app' => $app->id]);
		}
		$backto = request()->query('backto');
		if((!$back_url || $backto == 'list') && Auth::user()->can('view-any', App::class)) {
			$back_url = route('admin.apps.index', ['goto_item' => $app->id]);
		}

		$data['app'] = $app;
		$data['max_visuals'] = Settings::get('app.visuals.max_amount');

		// Get the errors bag
		// https://stackoverflow.com/questions/23137627/accessing-errors-in-a-controller-from-a-redirect-laravel
		$errors = session()->get('errors', app(ViewErrorBag::class));
		// dd($errors, session()->get('errors'));

		// Need to kidnap viso.* errors to display them with the js-generated form fields
		$viso_payload = old('viso', []);
		if($errors->has('viso.*')) {
			$cerrors = app(ViewErrorBag::class);
			$cerrors->put('default', $cerrors->getBag('default'));
			foreach($errors->messages() as $field => $errs) {
				if(Str::startsWith($field, 'viso.')) {
					$index = explode('.', $field)[1];
					$viso_payload[$index]['message'] = array_merge($viso_payload[$index]['message'] ?? [], $errs);
				} else {
					$cerrors->merge([$field => $errs]);
				}
			}
			foreach($viso_payload as $k => $item) {
				if(isset($item['message'])) {
					$viso_payload[$k]['message'] = implode('<br>', $item['message']);
				}
			}
		} else {
			$cerrors = $errors;
		}
		// Remove empty viso
		foreach($viso_payload as $k => $item) {
			if(empty($item['type']) && empty($item['value'])) {
				unset($viso_payload[$k]);
			}
		}

		$data['viso_payload'] = $viso_payload;
		$data['cerrors'] = $cerrors;

		$data['user'] = Auth::user();
		$data['back'] = $back_url;
		$data['old_uploads'] = old('new_images', []);

		$data['pending_edits'] = settings('app.modification_needs_verification', false);
		$data['caption_limit'] = settings('app.visuals.caption_limit', 300);

		return view('admin/app/visuals', $data);
	}

	// POST
	public function updateVisuals(Request $request, App $app)
	{
		//
		if($app->has_floating_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app, false, false);
		}
		$verf_edit = settings('app.modification_needs_verification', false);

		request_replace_nl($request);
		$rules = [
			'visuals_count'		=> ['required'], // dummy field for validation
			'visuals'			=> ['nullable', 'array'],
			'visuals.*.id'		=> ['required', 'integer', new ModelExists(AppVisualMedia::class)],
			'visuals.*.order'	=> ['nullable', 'integer'],
			'visuals.*.caption'	=> ['nullable', 'string'],
			'visuals.*.delete'	=> ['nullable'],
			// 'new_images.*'		=> ['nullable', 'string'],
			'viso'				=> ['nullable', 'array'], // TODO: add checking whether visuals has reached max or not
			'viso.*.type'		=> ['required', 'string'], // TODO: check against type whitelist
			'viso.*.value'		=> ['required', 'string'],
		];

		if($caption_limit = settings('app.visuals.caption_limit', 300))
			$rules['visuals.*.caption'][] = 'max:'.$caption_limit;

		// TODO: field names
		$messages = [
			'visuals.*.id'		=> 'Terjadi kesalahan: item tidak ditemukan.',
			'viso'				=> 'Maksimal jumlah lang:visuals adalah x',
			// 'viso.*.type'		=> '', // just change the attribute name
			// 'viso.*.value'		=> '', // just change the attribute name
		];

		// Dynamically add rules concerning the viso value based on its type
		if($request->has('viso') && is_array($request->viso)) {
			foreach($request->viso as $i => $item) {
				$rule_name = 'viso.'.$i.'.value';
				$item_rule = [];
				// TODO: add more types
				switch($item['type']) {
					case 'video.youtube':
						$item_rule[] = 'url';
						break;
				}

				$rules[$rule_name] = $item_rule;
			}
		}

		$input_visuals = $request->input('visuals', []);
		$new_images_hash = $request->input('new_images', []);
		$input_viso = $request->input('viso', []);
		$viso_not_empty = collect($input_viso)->filter(function($item) {
			return !empty($item['type']) && !empty($item['value']);
		});

		$deleted = collect();
		$not_deleted = collect();
		foreach($input_visuals as $ivis) {
			if(isset($ivis['delete']) && $ivis['delete'] == 1) {
				$deleted[] = $ivis;
			} else {
				$not_deleted[] = $ivis;
			}
		}

		// Make sure total visuals does not exceed maximum amount
		$max_visuals = Settings::get('app.visuals.max_amount');
		if($request->has('new_images') || count($viso_not_empty) > 0) {
			$rules['visuals_count'][] = function($attr, $value, $fail) use($max_visuals, $not_deleted, $new_images_hash, $viso_not_empty) {
				$new_count = count($not_deleted) + count($new_images_hash) + count($viso_not_empty);
				if($new_count > $max_visuals) {
					// TODO: move message somewhere else
					$fail('Max amount of visuals ($max_visuals) reached).');
				}
			};
		}

		// Validate
		$validData = $request->validate($rules);

		// Validate files
		$validFiles = Filepond::field($new_images_hash)->validate([
			'new_images.*'	=> ['nullable', 'file', 'max:2048'], // TODO: settings on max image size
		]);

		// TODO: prepare for app diff
		AppManager::prepareForVersionDiff($app);
		$visuals_by_id = $app->visuals->keyBy('id');

		$result = true;
		$error = [];
		$uploaded_files = [];
		DB::beginTransaction();
		try {
			// Reorder items
			$rel_visuals = $app->visuals->keyBy('id');

			// Delete items first
			foreach($deleted as $vdel) {
				$vis = $visuals_by_id[$vdel['id']];
				$vis->order = 99;

				// TODO: Respect verf settings
				// $result = $result && $vis->delete(); // make sure is soft-delete
				$vis->deleted_at = $vis->freshTimestampString();
				unset($rel_visuals[$vis->id]);
			}

			// Normalize order so that it's always sequential
			$ordered = $not_deleted->keyBy('id')->sortBy('order');
			$visorder = 0;
			foreach($ordered as $ivis) {
				$vis = $visuals_by_id[$ivis['id']];
				$vis->order = ++$visorder; // instead of the order input, because deleted files are not counted
				$vis->caption = $ivis['caption'];

				// TODO: Respect verf settings
				// $result = $result && $vis->save();
			}


			// Make sure storage dir exists
			$storage = Storage::disk('public');
			$storage_rel_path = 'apps/'.$app->id.'/';
			$storage_path = $storage->path($storage_rel_path);
			if(!$storage->has($storage_rel_path)) {
				$storage->createDir($storage_rel_path);
			}

			// Process the new images
			$new_images_result = true;
			$new_images_files = Filepond::field($new_images_hash)->getFile();
			if($new_images_files) {
				// Resize images and convert to jpeg, to standardize and
				// (possibly) to reduce bandwidth consumption
				$small_resize = settings('app.visuals.image_small_size');
				list($small_maxw, $small_maxh) = $small_resize;
				$small_resize = $small_maxw && $small_maxh;
				foreach($new_images_files as $imgfile) {
					// Try to process the image anyway to potentially remove malicious codes
					// inside the file.
					$img = new ImageResize($imgfile->getPathname());

					// Random filename
					$filename = $imgfile->hashName();
					$finfo = pathinfo($filename);
					$extension = strtolower($finfo['extension']);
					$barename = $finfo['filename'];
					if(!in_array($extension, ['png', 'jpg', 'jpeg'])) {
						// Convert to JPG if it's non standard
						$extension = 'jpg';
						$fname = $barename.'.'.$extension;
						$convert = true;
					} else {
						$fname = $filename;
						$convert = false;
					}

					try {
						// The original media
						$fpath = $storage_path.$fname;
						$img->scale(100)->save($fpath, $convert ? IMAGETYPE_JPEG : null);
						$uploaded_files[] = $fpath;

						// Only store rescaled version if not small
						if(!$small_resize
							|| ($img->getSourceWidth() <= $small_maxw
								&& $img->getSourceHeight() <= $small_maxh)
						) {
							// Small = original
							$small_fname = $fname;
						} else {
							// The scaled down media to save bandwidth
							$small_fname = $barename.'-sm.'.$extension;
							$small_fpath = $storage_path.$small_fname;
							$img->resizeToBestFit($small_maxw, $small_maxh)->save($small_fpath, IMAGETYPE_JPEG);
							$uploaded_files[] = $small_fpath;
						}
					} catch(\Exception $e) {
						$new_images_result = false;
						$error[] = 'Gagal memproses gambar unggahan: '. $imgfile->getClientOriginalName(); // TODO: fix message
						continue; // TODO: continue or break?
					}

					// Gather image meta, store as json string
					$meta = [
						'dimensions'	=> $img->getSourceWidth() .'x'. $img->getSourceHeight(),
						'size'			=> bytes_to_text($imgfile->getSize()),
						'extension'		=> strtoupper($extension),
					];

					// Save the model
					$vis = new AppVisualMedia;
					$vis->app_id = $app->id;
					$vis->order = ++$visorder;
					$vis->type = AppVisualMedia::TYPE_IMAGE;
					$vis->media_name = $fname;
					$vis->media_path = $storage_rel_path.$fname;
					$vis->media_small_name = $small_fname;
					$vis->meta = $meta;
					// NOTE: Respect verf settings, undelete later
					$vis->deleted_at = $vis->freshTimestampString();

					$new_images_result = $new_images_result && $vis->save();
					$rel_visuals[$vis->id] = $vis;
				}
			}

			$result = $result && $new_images_result;
			// TODO: do something with the message

			// Insert non images
			foreach($viso_not_empty as $viso) {
				$vis = new AppVisualMedia;
				$vis->app_id = $app->id;
				$vis->order = ++$visorder;
				$type = explode('.', $viso['type']);
				$vis->type = $type[0];
				$vis->subtype = $type[1];

				$value = null;
				if($viso['type'] == 'video.youtube') {
					// TODO: get video meta from URL maybe...?
					$value = get_youtube_id_from_url($viso['value']);
					$meta = [
						'youtube_id'	=> $value,
						'url'			=> get_youtube_url($value),
					];
					$vis->meta = $meta;
				}

				$vis->media_name = $value;
				$vis->media_path = $viso['value'];

				// NOTE: Respect verf settings, undelete later
				$vis->deleted_at = $vis->freshTimestampString();

				$result = $result && $vis->save();
				$rel_visuals[$vis->id] = $vis;
			}

			$app->setRelation('visuals', elocollect($rel_visuals->values()->all()));

			if($result) {
				$changes = AppManager::diffSave($app);
				$result = $changes['status'];
			}

			// Delete temp files
			if($result && $new_images_result) {
				Filepond::field($new_images_hash)->delete();
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = FALSE;
			// TODO: do something with the message
			$error[] = $e->getMessage();
			// dd($e->getMessage());
		}

		if(!$result) {
			DB::rollback();

			// Delete uploaded files
			foreach($uploaded_files as $fpath) {
				if(file_exists($fpath)) {
					@unlink($fpath);
				}
			}

			// TODO: Pass a message...?
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.message.save_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($error);
		} else {
			DB::commit();

			if(!empty($changes['changes'])) {
				// Pass a message
				$request->session()->flash('flash_message', [
					'message'	=> __('admin/apps.messages.update_successful'),
					'type'		=> 'success'
				]);
				if($verf_edit && !$app->is_unverified_new) {
					$request->session()->flash('messages', __('admin/apps.messages.update_successful_pending'));
				}
			}

			$back_after_save = $request->input('back_after_save', 0) == 1;
			if($back_after_save && Auth::user()->can('view', $app)) {
				return redirect()->route('admin.apps.show', ['app' => $app->id]);
			} elseif(Auth::user()->can('update', $app)) {
				return redirect()->route('admin.apps.visuals', ['app' => $app->id]);
			} elseif(Auth::user()->can('view-any', $app)) {
				return redirect()->route('admin.apps.index', ['goto_item' => $app->id, 'goto_flash' => 1]);
			}

			return redirect()->back();
		}
	}

	public function reviewChanges(Request $request, App $app)
	{
		// Review the approved changes and compare between old and new item
		$approved_changes = $app->approved_changes;
		$verifs = $app->latest_approved_verifications;
		list($ori, $app, $changes) = AppManager::getPendingVersion($app, $approved_changes);

		$ori->load('latest_approved_verifications.changelogs');

		$verif = new AppVerification;
		$verif->app_id = $ori->id;
		$verif->setRelation('changelogs', $approved_changes->reverse()->values());

		$summary = new AppChangelog;
		$summary->app_id = $ori->id;
		$summary->diffs = $changes;

		$data = [
			'app'		=> $app,
			'ori'		=> $ori,
			'changes'	=> $changes,
			'verif'		=> $verif,
			'verifs'	=> $verifs,
			'summary'	=> $summary,
		];

		return view('admin/app/publish', $data);
	}

	// POST
	public function publishChanges(Request $request, App $app)
	{
		// Commit the approved changes
		// TODO: outsource the publish code to the manager, so that an
		// 'auto-commit-upon-approval' setting can be used

		// dd($request->input());

		$rules = [
			// 'versionb'			=> ['required'],
			'verif_ids'	=> [ // TODO: message for if this fails (e.g going back to page or manually entering the address to bypass reports)
				'required',
				new ModelExists(AppVerification::class, 'id', ',', function($query) use($app) {
					$query->where('app_id', $app->id);
				}),
			],
		];
		$validData = $request->validate($rules);

		$input_verif_ids = explode(',', $request->input('verif_ids'));
		$input_verif_ids = array_filter(array_unique($input_verif_ids));
		$user = Auth::user();

		// Begin storing entries
		DB::beginTransaction();

		$result = true;
		$error = [];
		try {
			// Find the verifications, then the changelogs, then apply them
			$changelogs = $app->changelogs()->inVerifIds($input_verif_ids)
				->orderBy('created_at')
				->orderBy('version')
				->get()
			;

			// Make sure all the changelogs status are approved
			$all_approved = $changelogs->every(function($item) {
				return $item->status == AppChangelog::STATUS_APPROVED;
			});
			// Huh, what to do if it's false...?
			if(!$all_approved) {
				throw new \UnexpectedValueException(__('admin/apps.messages.the_changelogs_data_are_corrupted'));
			}

			// Only publish if it's new, otherwise don't change the status
			$apply_only = $request->input('apply_only', 0) == 1;
			if($apply_only) {
				$publish = false;
			} else {
				$publish = $app->is_unverified_new;
			}
			$result = AppManager::verifyAndApplyChanges($app, $changelogs, $publish, $user);
		} catch(\Exception $e) {
			$result = FALSE;
			// TODO: do something with the message
			$error[] = $e->getMessage();
			// dd($e->getMessage());
		}

		if(!$result) {
			DB::rollback();

			// Pass a message...?
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($error);
		} else {
			DB::commit();

			// Pass a message
			// TODO: maybe different messages for when it's an edit/new thing?
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/apps.messages.update_successful'),
				'type'		=> 'success'
			]);
			$request->session()->flash('post_publish', true);

			return redirect()->route('admin.apps.published', ['app' => $app->id]);
		}
	}

	public function afterPublishChanges(Request $request, App $app) {
		$data = [];

		$data['app'] = $app;

		return view('admin/app/publish-after', $data);
	}

	public function setPrivate(Request $request, App $app, $private = null) {
		$this->authorize('set-private', $app);

		// Simple action, like delete

		$private = $private === null ? 1 : intval($private);
		$private = $private == 0 ? false : true;

		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$app->setToPrivate($private);
			$result = $app->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if($result) {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> $private
					? __('admin/apps.messages.app_was_made_private')
					: __('admin/apps.messages.app_was_made_not_private'),
				'type'		=> 'success'
			]);
		} else {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.action_failed'),
				'type'		=> 'danger'
			]);
		}

		$redirect = null;
		$backto = request()->query('backto');
		if(Auth::user()->can('view', $app)) {
			$redirect = route('admin.apps.show', ['app' => $app->id]);
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

	public function setPublished(Request $request, App $app, $published = null) {
		$this->authorize('set-published', $app);

		// Simple action, like delete

		$published = $published === null ? 1 : intval($published);
		$published = $published == 0 ? false : true;

		DB::beginTransaction();

		$result = true;
		$messages = [];
		try {
			$app->setToPublished($published);
			$result = $app->save();
		} catch(\Illuminate\Database\QueryException $e) {
			$result = false;
			$messages[] = $e->getMessage();
		}

		if($result) {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> $published
					? __('admin/apps.messages.app_was_published')
					: __('admin/apps.messages.app_was_unpublished'),
				'type'		=> 'success'
			]);
		} else {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/common.messages.action_failed'),
				'type'		=> 'danger'
			]);
		}

		$redirect = null;
		$backto = request()->query('backto');
		if(Auth::user()->can('view', $app)) {
			$redirect = route('admin.apps.show', ['app' => $app->id]);
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

	public function snippetVisualsComparison(Request $request, $app = null, $version = null)
	{
		if(!$app && $request->has('app_id')) {
			$app = $request->input('app_id');
		}
		$app = App::findOrFail($app);

		if($version === null && $request->has('version'))
			$version = $request->input('version');

		if($version) {
			$cl = $app->changelogs()->where('version', $version)->firstOrFail();
			$diffs = AppManager::transformDiffsForDisplay($cl->diffs);
			if(isset($diffs['relations']['visuals'])) {
				$items = $diffs['relations']['visuals'];
				$items['new'] = $items['new']->sortBy('order');
				$items['old'] = $items['old']->sortBy('order');
			} else {
				$items = [
					'new'	=> collect(),
					'old'	=> collect(),
				];
			}
		} elseif($request->has('new') && $request->has('old')) {
			// TODO: what about the items order?
			$new = explode(',', $request->input('new'));
			$old = explode(',', $request->input('old'));

			$new_items = $app->visuals()->withTrashed()->find($new)->keyBy('id');
			// Reorder according to input order
			foreach($new as $i => $id) {
				$new_items[$id]->order = $i + 1;
			}
			$new_items = $new_items->sortBy('order');

			$old_items = $app->visuals()->withTrashed()->find($old)->keyBy('id');
			// Reorder according to input order
			foreach($old as $i => $id) {
				$old_items[$id]->order = $i + 1;
			}
			$old_items = $old_items->sortBy('order');

			$items = [
				'new'	=> $new_items,
				'old'	=> $old_items,
			];
		}

		if(empty($items)) {
			echo 'Please specify version or items to be compared'; // TODO message
			return;
		}

		// dd($items);

		$simple = $request->input('simple', '0') == '1';
		$load_library = !$simple && $request->input('init', '0') == '1';
		$autoplay = !$simple && $request->input('autoplay', '0') == '1';
		$image_only = $request->input('complete', '0') == '0';

		return view('admin/app/changes/relations/visuals-comparison-snippet', [
			'items'	=> $items,
			'simple' => $simple,
			'load_library' => $load_library,
			'autoplay' => $autoplay,
			'image_only_mode' => $image_only,
		]);
	}

	public function snippetVersionDetail(Request $request, $app = null, $version = null)
	{
		if(!$app && $request->has('app_id')) {
			$app = $request->input('app_id');
		}
		$app = App::findOrFail($app);
		$data = [];

		$verif_id = $request->input('verif_id');
		$verif = $app->verifications()->find($verif_id);

		if($version === null)
			$version = $request->input('version');

		if($verif) {
			// Gather all changes
			$version_item = $verif->changelogs->first();
			if($version_item) {
				// TODO: compile changes based on verif status, i.e:
				// for approved and/or needs-revision, mock the last version
				// for rejected, mock the first version only (and maybe inform that the later versions also gets rejected...?)
				$version_item->diffs = AppManager::compileVersionsChanges($verif->changelogs->reverse()->values());
				$data['version'] = $version_item;
				$data['verif'] = $verif;

				// Mock the latest version affected by this verification
				$app = AppManager::getMockItem($app->id, $version_item->version);
			}
		} elseif($version) {
			// Make item first
			$version_item = $app->changelogs()->where('version', $version)->first();
			if($request->input('accumulate_changes', 0) == 1 && $version > $app->version_number) {
				// Accumulate changes and actually compare to the current version
				$version_item->diffs = AppManager::getVersionsChanges($app, $version)['changes'];
			}
			$data['version'] = $version_item;
			$data['show_version_status'] = true;
			$app = AppManager::getMockItem($app->id, $version);
		}

		$data['app'] = $app;
		$data['view_only'] = $request->input('view_only', 0) == 1;
		return view('admin/app/detail-snippet', $data);
	}

	public function jsonPendingVersions(Request $request, $app = null)
	{
		if(!$app && $request->has('app_id')) {
			$app = $request->input('app_id');
		}
		$app = App::findOrFail($app);

		// Ajax

		$versions = $app->floating_changes;
		$versions = $versions->map(function($v, $k) {
			return $v->only('id', 'version');
		})->reverse()->values();
		return response()->json($versions->all());
	}
}
