<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppChangelog;
use App\Models\AppChangelogCollection;
use App\Models\AppVerification;
use App\Models\VerificationStatus;
use App\Models\VerifierVerificationStatus as VVStatus;
use App\Models\EditorVerificationStatus as EVStatus;

use App\DataManagers\AppManager;


use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Str;

class AppVerificationController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	public static function listQuery($filters = null) {

		$default_filters = [
			'status'	=> 'unverified',
		];
		if($filters) {
			$filters = get_filters($filters, $default_filters);
		} else {
			$filters = $default_filters;
		}
		$opt_filters = optional($filters);
		$filter_count = 0;

		$query = (new App)->newQueryWithoutScopes();
		$query->from('apps as a');
		$query->leftJoin('app_changelogs as cv', 'a.version_id', '=', 'cv.id');
		$query->leftJoin('app_changelogs as cl', function($query) {
			$query->on('a.id', '=', 'cl.app_id');
			$query->where('cl.status', AppChangelog::STATUS_PENDING);
			$query->whereRaw('if(cv.id is not null, cl.created_at >= cv.created_at and cl.id >= cv.id, 1)');
		});
		$query->leftJoin('users as o', 'a.owner_id', '=', 'o.id');
		$query->leftJoin('ref_prodi as prodi', 'o.prodi_id', '=', 'prodi.id');

		// Do not include trashed/deleted items
		$query->whereNull('a.deleted_at');

		// Role/ability scoping filter
		$view_mode = 'none';
		AppManager::scopeListQuery($query, $view_mode, false);

		$total = (clone $query)->count(DB::raw('distinct a.id'));

		$query->select('a.*');
		$query->groupBy('a.id');
		$query->orderBy('a.updated_at', 'desc');
		$query->orderBy('a.id', 'desc');

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

		if($filters['status'] == 'unverified') {
			$query->whereNotNull('cl.id');
		} elseif($filters['status'] == 'verified') {
			$query->whereNull('cl.id');
			$filter_count++;
		} else {
			if($opt_filters['status'] == 'all') {
				$filter_count++;
			}
		}

		return compact('query', 'filters', 'filter_count', 'total', 'view_mode');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$this->authorize('view-any', AppVerification::class);

		//
		$data = [];
		$user = Auth::user();

		extract( static::listQuery(['keyword', 'status']) );

		$items = $query->paginate(10);
		$items->appends($filters);

		$data['total'] = $total;
		$data['items'] = $items;
		$data['filters'] = optional($filters);
		$data['filter_count'] = $filter_count;
		$data['view_mode'] = $view_mode;
		$data['prodi'] = optional($user->prodi);

		return view('admin/app_verification/index', $data);
	}

	public function review(Request $request, App $app, $verif = null)
	{
		$this->authorize('review', [AppVerification::class, null, $app]);

		//
		$data = [];

		// In case of edit
		$verif = AppVerification::find($verif);
		$is_edit = false;
		// Show the latest edits if there are any pending changes
		if(!$verif) {
			$versions = $app->floating_changes;

			$verif = new AppVerification;
			$verif->related_versions = $app->has_floating_changes
				? $versions->pluck('version')->sort()->values()->implode(',')
				: $app->version_number
			;
			$verif->base_version = $app->version_number ?? $app->changelogs()->oldest()->value('version');

			if(count($versions) > 0) {
				list($ori, $app, $all_changes) = AppManager::getPendingVersion($app);
			} else {
				// NOTE: use find() to generate a new, separate object
				$ori = $app->find($app->getKey());
				$version = $app->version;
				$versions = elocollect([$version]);
				$all_changes = [];
				if($version) {
					$version->diffs = [];
				}
			}
		} else {
			// Additional gate checks
			$is_edit = true;
			$this->authorize('update', $verif);

			$versions = $verif->changelogs->reverse()->values();

			$verif->base_version = $verif->base_changelog->version;
			list($ori, $app, $all_changes) = AppManager::getPendingVersion($app, $versions);
		}

		if(count($versions) > 0) {
			$verif->related_versions = $versions->pluck('version')->sort()->values()->implode(',');
			$version = $versions->last();
			// $app->setRelation('version', $version);
			if($version) {
				$version->diffs = $all_changes;
			}
		} else {
			$verif->related_versions = $app->version_number;
		}

		$data['ori'] = $ori;
		$data['app'] = $app;

		// Can't do the following because any assignment gets cast back
		// into an array internally by the model
		// $verif->details = optional($verif->details);
		// So assign it to a dummy attribute
		$verif->attrs = optional($verif->details);


		$data['verif'] = $verif;
		$data['version'] = $version;
		$data['versions'] = $versions;
		$data['versions_range'] = new AppChangelogCollection($versions->all());
		// dd($versions, $versions->pluck('version')->implode(','));

		$verif_report = null;
		if($ori->is_reported) {
			$verif_report = $ori->report_verification;
		}
		$data['verif_report'] = $verif_report;

		$data['vstatus'] = VVStatus::all()->keyBy('id');

		$data['post_verif_status'] = session('post_verif_status');

		$data['goto_form'] = $request->input('goto_form', 0) == 1;
		$data['goto_history'] = $request->input('goto_history', 0) == 1;

		return view('admin/app_verification/review', $data);
	}

	public function verify(Request $request, App $app)
	{
		$this->authorize('review', [AppVerification::class, null, $app]);

		//
		$id = $request->input('id');
		$is_edit = !empty($id);

		// Make verification item
		if(!$is_edit) {
			$ver = new AppVerification;
			$ver->app_id = $app->id;
			$ver->verifier_id = $request->user()->id;
		} else {
			$ver = AppVerification::find($id);

			// Additional gate checks
			$this->authorize('update', $ver);
		}

		request_replace_nl($request);
		$rules = [
			// 'dummy'				=> ['required'],
			'base_version'		=> [
				'required',
				new ModelExists(AppChangelog::class, 'version', null, function($query) use($app) {
					$query->where('app_id', $app->id);
				}),
			],
			'related_versions'	=> [
				'required',
				new ModelExists(AppChangelog::class, 'version', ',', function($query) use($app) {
					$query->where('app_id', $app->id);
				}),
			],
			'details'			=> ['array'],
			'details.*'			=> ['nullable', 'string', 'max:200'],
			'overall_comment'	=> ['required', 'string', 'max:1000'],
			'verif_status'		=> ['required', new ModelExists(VVStatus::class)],
		];
		$field_names = [
			'base_version'		=> __('admin/app_verifications.base_version'),
			'related_versions'	=> __('admin/app_verifications.related_versions'),
			'details.*'			=> __('admin/app_verifications.field.attribute_comment'),
			'overall_comment'	=> __('admin/app_verifications.field.overall_comments'),
			'verif_status'		=> __('admin/app_verifications.field.verification_status'),
		];
		$validData = $request->validate($rules, [], $field_names);

		$input_versions = explode(',', $request->input('related_versions'));
		$input_versions = array_filter(array_unique($input_versions));

		// Begin storing entries
		DB::beginTransaction();

		$result = true;
		$error = [];
		try {
			$verif_status = $request->input('verif_status');
			$ver->status_id = $verif_status;
			$ver->comment = $request->input('overall_comment');

			$details = collect($request->input('details'))->filter()->sortKeys();
			$ver->details = $details->all();

			$base_version = $app->changelogs()->where('version', $request->input('base_version'))->first();
			if($base_version) {
				$ver->base_changes_id = $base_version->id;
			}

			$is_dirty = $ver->isDirty();
			if($is_edit && !$is_dirty) {
				// Don't do anything if no modifications
			} else {
				$result = $ver->save();

				// Set the verification's related changelog(s)
				if(!$is_edit) {
					$related_versions = $app->changelogs()->whereIn('version', $input_versions)->get()->reverse()->values();
				} else {
					$related_versions = $ver->changelogs->reverse()->values();
				}
				$last_version = $related_versions->last();

				if($verif_status == 'approved') {
					// Approved changes shouldn't be counted as pending changes anymore,
					// because they got applied - i.e they're not pending anymore.
					foreach($related_versions as $rv) {
						$rv->status = AppChangelog::STATUS_APPROVED;
					}
				} elseif($verif_status == 'rejected') {
					// Set all related changes to rejected...
					// Rejected changes shouldn't be counted as pending changes anymore,
					// i.e they're considered lost.
					foreach($related_versions as $rv) {
						$rv->status = AppChangelog::STATUS_REJECTED;
					}
				} else {
					// Assume revision-needed

					// Since it's being reviewed, there should be no change to the
					// related changes' state.
					foreach($related_versions as $rv) {
						$rv->status = AppChangelog::STATUS_PENDING;
					}
				}

				// Set verified state of the related changes
				foreach($related_versions as $rv) {
					$rv->is_verified = true;
					$result = $result && $rv->save();
				}

				// Pair the changes with the verification
				if(!$is_edit) {
					$ver->changelogs()->detach();
					$ver->changelogs()->attach($related_versions->modelKeys());
				}

				// Auto commit
				$auto_commit = settings('app.verification.auto_commit_upon_approval', false);
				if($result && $auto_commit) {
					$result = AppManager::verifyAndApplyChanges($app, $related_versions, false);
				}
			}
		} catch(\Illuminate\Database\QueryException $e) {
			$result = FALSE;
			$error[] = $e->getMessage();
		}

		if(!$result) {
			DB::rollback();

			// Pass a message
			$request->session()->flash('flash_message', [
				'message'	=> __('admin/app_verifications.messages.verify_failed'),
				'type'		=> 'error'
			]);

			return redirect()->back()->withInput()->withErrors($error);
		} else {
			DB::commit();

			if(!$is_edit) {
				// Pass a message
				$request->session()->flash('flash_message', [
					'message'	=> __('admin/app_verifications.messages.verify_successful'),
					'type'		=> 'success'
				]);
				$request->session()->flash('post_verif_status', $verif_status);
			} else {
				if($is_dirty) {
					// Pass a message
					$request->session()->flash('flash_message', [
						'message'	=> __('admin/app_verifications.messages.verify_edited'),
						'type'		=> 'success'
					]);
					$request->session()->flash('post_verif_status', $verif_status);
				}
			}

			return redirect()->route('admin.app_verifications.review', ['app' => $app->id, 'goto_form' => 1]);
		}
	}

	public function snippetDetail(Request $request, $verif_id = null)
	{
		$data = [];

		if($verif_id === null)
			$verif_id = $request->input('verif_id');

		$verif = AppVerification::findOrFail($verif_id);
		$app = $verif->app;

		// Gather all changes

		// TODO: compile changes based on verif status, i.e:
		// for approved and/or needs-revision, mock the last version
		// for rejected, mock the first version only (and maybe inform that the later versions also gets rejected...?)
		$version_item = $verif->changelogs->first();
		if($version_item) {
			$version_item->diffs = AppManager::compileVersionsChanges($verif->changelogs->reverse()->values());
		} else {
			// Mock
			$version_item = $app->version ?? new AppChangelog;
			$version_item->diffs = [];
		}
		$data['version'] = $version_item;
		$data['verif'] = $verif;

		// Mock the latest version affected by this verification
		$app = AppManager::getMockItem($app->id, $version_item->version);

		$data['app'] = $app;
		return view('admin/app_verification/detail-snippet', $data);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		//
	}

}
