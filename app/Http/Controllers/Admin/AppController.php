<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppCategory;
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
use App\Rules\DirectoryPath;
use App\Rules\FQDN;
use App\Rules\ModelExists;

use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

	protected $provider;

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
		$data = [];
		$apps = Auth::user()->apps()->get();
		$data['verified'] = $apps->where('is_verified', 1);

		$data['unverified'] = $apps->where('is_verified', 0);

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
		$app = new App;

		$data['app'] = $app;
		$data['is_edit'] = FALSE;

		$data['action'] = route('admin.apps.store');
		$data['method'] = 'POST';

		return view('admin/app/edit', $data);
	}

	protected function _store($request, $app = NULL) {

		$is_edit = $app instanceof App;
		if(!$is_edit) {
			$app = new App;
		} elseif($app->has_pending_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
		}

		$user = $request->user();
		$user_id = $user->id;

		$logo_input_hash = $request->input('app_logo');
		$delete_files = array();
		$uploaded_files = array();

		$verf_add = settings('app.creation_needs_verification', false);
		$verf_edit = settings('app.modification_needs_verification', false);


		// Validation rules
		$rules = [
			'app_name'			=> ['required', 'max:100'],
			'app_short_name'	=> ['nullable', 'max:20'],
			'app_description'	=> ['nullable'],
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
			'app_logo'	=> ['nullable', 'file', 'image', 'max:2048'], // TODO: settings on max image size
		]);

		AppManager::prepareForVersionDiff($app);

		if(!$is_edit) {
			$app->owner_id		= $user_id;
		} else {
			// App has to be owned by user to edit.
			// TODO: also allow superadmins and others to edit.
			if($user_id != $app->owner_id) {
				// Not allowed
				$request->session()->flash('flash_message', [
					'message'	=> __('admin.app.message.edit_failed_not_owner'),
					'type'		=> 'error'
				]);
				return redirect()->route('admin.apps.index');
			}
		}

		$app->name			= $request->app_name;
		$app->slug			= Str::slug($request->app_name);
		$app->short_name	= $request->has('app_has_short_name') ? $request->app_short_name : null;
		$app->description	= $request->app_description;

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
				// TODO: if verification is used, attach/detach relationships later

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


			// Visuals
			if($is_edit && $result && $vis_del = $request->input('visual_delete')) {
				foreach($vis_del as $vis_id) {
					$vis = AppVisualMedia::find($vis_id);
					if($vis) {
						// No deleting files, because they are trashed and is kept...?
						// $delete_files[] = $vis['media_path'];
						if(!$is_edit) {
							// No need to delete on edit because we make our own
							// collection anyway below
							// TODO: but deletion only exists in an edit lol
							$vis->delete();
						}
					}
				}
			}
			if($result && $visuals = $request->file('visuals')) {
				$rel_visuals = elocollect();
				foreach($visuals as $file) {
					// Upload and store each file
					$fpath = Storage::disk('public')->putFile('apps/'.$app->id.'/visuals', $file);
					if($fpath) {
						$fname = basename($fpath);

						$vis = new AppVisualMedia;
						$vis->caption = NULL; // DUMMY
						$vis->media_name = $fname;
						$vis->media_path = $fpath;
						$vis->app_id = $app->id;

						if($is_edit) {
							// Default trashed, will need to apply diff to be up
							// TODO: check staging config
							// TODO: how does this interact with diffing? save now or later?
							//  How would the differ diff between deleted items and new items
							//  if we immediately delete new items?
							//  Do we regress the app after diffing...?
							//  Can we make our own collection here then discard it?
							//  (instead of saving it because the differ does not
							//  load a fresh relation, but just uses any existing ones)
							$vis->deleted_at = $vis->freshTimestampString();
						} else {
							// Need the item to exist first on addition
							$result = $result && $app->visuals()->save($vis);
						}

						$rel_visuals[] = $vis;
						$uploaded_files[] = $fpath;
					} else {
						$result = FALSE;
					}

					if(!$result) {
						break;
					}
				}

				// Can't set like `$app->visuals = $value` because relations are
				// dynamically accessed properties (not real object properties)
				$app->setRelation('visuals', $rel_visuals);
			}


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
			if($result) {
				$automator = Automator::instance();

				$ver = new AppVerification;
				$ver->verifier_id = $automator->id;

				if(!$is_edit) {
					// Generate first verification step
					$ver->status_id = 'unverified';
					$ver->comment = 'new';
					$result = $result && !! $app->verifications()->save($ver);
				} elseif(!empty($changes['diffs'])) {
					// Revert verification status if:
					// 1. App is edited, and
					// 2. App is already verified.
					// TODO: check config for auto-unverify
					if($app->is_verified) {
						$ver->status_id = 'resubmitted';
						$ver->comment = 'status revert because of edit after approval';

						$result = !! $app->verifications()->save($ver);

						// NOTE: don't use the existing $app object because it's used
						// to mock changes. Get a new one instead
						// NOTE: no need to do this here because the app stays at
						// the same, approved version
						/*$apptemp = $app->find($app->getKey());
						$apptemp->setNextActionActor($automator->id);
						$apptemp->is_verified = false;
						$result = $result && $apptemp->save();*/
					} else {
						// Change status to revised if the current status is verifier-acted
						// Que: compile changes to any existing verifications, or
						// just make a new entry and then do the compilation somewhere
						// else down the line (e.g when opening the verification page)?

						// TODO: compile somewhere else, e.g when viewing a verification's
						// pending changes
						// If the last verification is verifier-acted, generate a
						// new verification step.
						// Else do nothing i guess?
						if($app->last_verification->status->by == 'verifier') {
							$ver->status_id = 'revised';

							$result = !! $app->verifications()->save($ver);
						}
					}
				}
			}
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

		return compact('result', 'messages');
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
				'message'	=> __('admin.app.message.create_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.app.message.create_successful'),
				'type'		=> 'success'
			]);

			return redirect()->route('admin.apps.index');
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function show(App $app, $snippet = false)
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
		if($app->has_pending_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
			$app->setRelation('version', $ori->pending_changes->last());
			$data['ori'] = $ori;
		}

		$data['app'] = $app;
		$data['is_edit'] = TRUE;

		$data['action'] = route('admin.apps.update', ['app' => $app->id]);
		$data['method'] = 'PATCH';

		$data['user'] = Auth::user();
		$data['old_uploads'] = old('app_logo', []);

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
		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.app.message.update_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($store['messages']);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.app.message.update_successful'),
				'type'		=> 'success'
			]);

			return redirect()->route('admin.apps.show', ['app' => $app->id]);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\App  $app
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(App $app)
	{
		//
	}

	public function verifications(App $app)
	{
		$data = [];
		$data['app'] = $app;

		$app->load(['verifications.verifier', 'verifications.status', 'actions_log.actor']);

		// Mix update actions with verification actions and sort them
		$updates = $app->actions_log()->whereIn('action', ['create', 'update'])->get();
		// dd($updates);
		$mixed = collect()->concat($app->verifications)->concat($updates)->map(function($item) {
			$item->at = $item->updated_at ?? $item->created_at ?? $item->at;
			return $item;
		})->sortBy('at');
		dd($mixed);

		$actions = collect();
		foreach($mixed as $item) {
			$tmp = new \stdClass;
			if($item instanceof AppVerification) {
				$tmp->type = 'verification';
				$tmp->actor = $item->verifier;
				$tmp->status = $item->status;
				if(in_array($tmp->status_id, ['approved', 'rejected'])) {
					$tmp->background = 'bg-'.$tmp->status->bg_style;
				} else {
					$tmp->background = 'bg-lightblue';
				}
				$tmp->field_comments = $item->details;
				$tmp->comment = $item->comment;
				// qwe
			} else {
				$is_create = $item->action == 'create';
				$tmp->type = 'owner';
				$tmp->actor = $item->actor;
				$tmp->status = VerificationStatus::find($is_create ? 'unverified' : 'revised');
				// asd
			}

			$tmp->background = $item->status->bg_style;
			$tmp->at = $item->at;
			$actions[] = $tmp;
		}

		$timeline = collect();
		/*foreach($app->verifications as $v) {
			$v->_date = $v->updated_at ?? $v->created_at;
			$v->_grouping = $group = $v->_date ? $v->_date->format('Y-m-d') : NULL;

			// $v->icon =

			if(!isset($timeline[$group])) {
				$timeline[$group] = (object) [
					'date'	=> $group,
					'text'	=> $v->_date->format('j F Y'),
					'items'	=> collect(),
				];
			}
			$timeline[$group]['items'][] = $v;
		}*/
		$data['timeline'] = $timeline->sortKeysDesc();
		// dd($app->verifications[0]->updated_at);

		return view('admin/app/verifications', $data);
	}

	public function changes(App $app)
	{
		$data = [];
		$data['app'] = $app;

		$per_page = 10;
		$page = request()->input('page', 1);
		$go_current = request()->has('current');
		if($go_current && $app->version_id) {
			// Find page
			// https://stackoverflow.com/questions/9086719/mysql-paginated-results-find-page-for-specific-result
			// Normally you'd do operator <= but since the order is desc, we use >=
			$offset = $app->changelogs()->where('id', '>=', $app->version_id)->count();
			$page = ceil($offset / $per_page);
		}

		$changelogs = $app->changelogs()->paginate($per_page, ['*'], 'page', $page);
		foreach($changelogs as $item) {
			$item->display_diffs = AppManager::transformDiffsForDisplay($item->diffs);
		}
		$data['changelogs'] = $changelogs;

		return view('admin/app/changes/index', $data);
	}

	public function visuals(App $app)
	{
		$data = [];

		$app->load('visuals');
		// Show the latest edits if there are any pending changes
		if($app->has_pending_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
			$app->setRelation('version', $ori->pending_changes->last());
			$data['ori'] = $ori;
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
		$data['old_uploads'] = old('new_images', []);

		$data['caption_limit'] = settings('app.visuals.caption_limit', 300);

		return view('admin/app/visuals', $data);
	}

	public function updateVisuals(Request $request, App $app)
	{
		//
		if($app->has_pending_changes) {
			list($ori, $app) = AppManager::getPendingVersion($app);
		}

		$rules = [
			'visuals_count'		=> ['required'], // dummy field for validation
			'visuals'			=> ['nullable', 'array'],
			'visuals.*.id'		=> ['required', 'integer', new ModelExists(AppVisualMedia::class)],
			'visuals.*.order'	=> ['nullable', 'integer'/*, 'max:10'*/],
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

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin.message.save_successful'),
				'type'		=> 'success'
			]);

			return $request->input('back_after_save', 0) == 1
				? redirect()->route('admin.apps.show', ['app' => $app->id])
				: redirect()->route('admin.apps.visuals', ['app' => $app->id])
			;
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

		$load_library = $request->input('init', '0') == '1';
		$autoplay = $request->input('autoplay', '0') == '1';
		$image_only = $request->input('complete', '0') == '0';

		return view('admin/app/changes/relations/visuals-comparison-snippet', [
			'items'	=> $items,
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

		if($version === null)
			$version = $request->input('version');

		if($version) {
			// Make item first
			$app = AppManager::getMockItem($app->id, $version);
		}

		$data = [];
		$data['app'] = $app;
		return view('admin/app/detail-snippet', $data);
	}

	public function jsonPendingVersions(Request $request, $app = null)
	{
		if(!$app && $request->has('app_id')) {
			$app = $request->input('app_id');
		}
		$app = App::findOrFail($app);

		// Ajax

		$versions = $app->pending_changes;
		$versions = $versions->map(function($v, $k) {
			return $v->only('id', 'version');
		})->reverse()->values();
		return response()->json($versions->all());
	}
}
