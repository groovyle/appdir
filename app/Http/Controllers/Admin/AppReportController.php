<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\AppChangelog;
use App\Models\AppChangelogCollection;
use App\Models\AppVerification;
use App\Models\AppReport;
use App\Models\AppVerdict;
use App\Models\UserBlock;
use App\User;

use App\DataManagers\AppManager;
use App\DataManagers\AppReportManager;


use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;
use Illuminate\Validation\Rule;

class AppReportController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	public static function listQuery($filters = null) {

		$default_filters = [
			'status'	=> 'unresolved',
		];
		if($filters) {
			$filters = get_filters($filters, $default_filters);
		} else {
			$filters = $default_filters;
		}
		$opt_filters = optional($filters);
		$filter_count = 0;

		$report_status_unresolved = AppReport::statusUnresolved();
		$report_status_resolved = AppReport::statusResolved();

		$query = (new App)->newQueryWithoutScopes();
		$query->from('apps as a');
		$query->leftJoin('app_reports as r', 'a.id', '=', 'r.app_id');
		$query->leftJoin('app_reports as rur', function($query) use($report_status_unresolved) {
			$query->on('a.id', '=', 'rur.app_id');
			$query->whereIn('rur.status', $report_status_unresolved);
		});
		$query->leftJoin('app_reports as rr', function($query) use($report_status_resolved) {
			$query->on('a.id', '=', 'rr.app_id');
			$query->whereIn('rr.status', $report_status_resolved);
		});
		$query->leftJoin('app_verdicts as vd', function($query) {
			$query->on('a.id', '=', 'vd.app_id');
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
		$query->selectRaw('count(distinct r.id) as num_reports');
		$query->selectRaw('max(r.updated_at) as last_report');
		$query->selectRaw('count(distinct rur.id) as num_unresolved_reports');
		$query->selectRaw('max(rur.updated_at) as last_unresolved_report');
		$query->selectRaw('count(distinct rr.id) as num_resolved_reports');
		$query->selectRaw('max(rr.updated_at) as last_resolved_report');

		$query->selectRaw('count(distinct vd.id) as num_verdicts');

		$query->groupBy('a.id');

		if($opt_filters['status'] == 'unresolved') {
			$query->whereNotNull('rur.id');
			// $query->orderBy('last_unresolved_report', 'desc');
			$query->orderBy('num_unresolved_reports', 'desc');
			// $filter_count++;
		} elseif($opt_filters['status'] == 'resolved') {
			$query->whereNotNull('rr.id');
			// $query->orderBy('last_resolved_report', 'desc');
			$query->orderBy('num_unresolved_reports', 'desc');
			$filter_count++;
		} else {
			// $query->orderBy('last_report', 'desc');
			$query->orderBy('num_unresolved_reports', 'desc');
			$query->orderBy('num_reports', 'desc');
			if($opt_filters['status'] == 'all') {
				$filter_count++;
			}
		}

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

		$query->orderBy('num_verdicts', 'desc');
		$query->orderBy('a.name', 'asc');
		$query->orderBy('a.id', 'desc');

		return compact('query', 'filters', 'filter_count', 'total', 'view_mode');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		$this->authorize('view-any', AppReport::class);
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

		return view('admin/app_report/index', $data);
	}

	public function review(Request $request, App $app)
	{
		$this->authorize('create', AppReport::class);
		//
		$data = [];

		$reports = $app->reports()->unresolved()->defaultOrder()->get();
		// $reports = collect();
		$reports_data = collect();
		foreach($reports as $r) {
			$reports_data[$r->id] = [
				'id'		=> $r->id,
				'reason'	=> $r->reason,
				'status'	=> $r->status,
				'version'	=> $r->version ? $r->version->version : '__none',
				'name'		=> optional($r->user)->name,
				'email'		=> $r->user_id ? $r->user->email : $r->email,
				'category'	=> $r->categories->pluck('id')->all(),
			];
		}
		$compiled_relations = AppReportManager::compileRelationsFromReports($reports);
		$all_categories = $compiled_relations['categories'];
		$all_versions = $compiled_relations['versions'];

		$data['app'] = $app;
		$data['reports'] = $reports;
		$data['reports_data'] = $reports_data;
		$data['all_categories'] = $all_categories;
		$data['all_versions'] = $all_versions;

		return view('admin/app_report/review', $data);
	}

	public function verify(Request $request, App $app) {
		$this->authorize('create', AppReport::class);

		request_replace_nl($request);
		$rules = [
			// '_dummy'			=> ['required'],
			'report'			=> ['nullable', 'array'],
			'report.*.id'		=> ['required', 'integer', new ModelExists(AppReport::class)],
			'report.*.status'	=> ['required', 'in:valid,invalid'],
			'final_comments'	=> ['required', 'string', 'min:50', 'max:1000'],
			'verdict'			=> ['required', Rule::in(AppVerdict::statusAll())],
			'block_user'		=> ['nullable'],
		];

		$verdict_status = $request->input('verdict');
		if($verdict_status == AppVerdict::STATUS_INNOCENT) {
			// Does being innocent mean ALL the reports have to be invalid?
			// TODO: error message for this
			$rules['verdict'][] = function($attr, $value, $fail) use($request) {
				$result = collect($request->input('report.*.status'))->every(function($item) {
					return $item == 'invalid';
				});
				if(!$result) {
					// TODO: move message somewhere else
					$fail('TODO: If the verdict is innocent, all the reports must be invalid.');
				}
			};
		} elseif($verdict_status == AppVerdict::STATUS_GUILTY) {
			// Does being guilty mean AT LEAST ONE the reports have to be valid?
			// TODO: error message for this
			/*$rules['verdict'][] = function($attr, $value, $fail) use($request) {
				$result = collect($request->input('report.*.status'))->contains('valid');
				if(!$result) {
					// TODO: move message somewhere else
					$fail('TODO: If the verdict is guilty, at least one of the reports must be valid.');
				}
			};*/
		}

		// TODO: field names
		$messages = [
			'report.*.id'		=> 'Terjadi kesalahan: item tidak ditemukan.',
		];


		// Validate
		$validData = $request->validate($rules);

		$result = true;
		$error = [];
		DB::beginTransaction();
		try {
			$input_final_comments = $request->input('final_comments');

			// Make verdict first
			$verdict = new AppVerdict;
			$verdict->app_id = $app->id;
			$verdict->version_id = $app->version_id;
			$verdict->comments = $input_final_comments;

			$details = [];
			$details_block_user = $request->input('block_user') == 1;
			if($verdict_status == AppVerdict::STATUS_INNOCENT) {
				$verdict->status = $verdict_status;
			} elseif($verdict_status == AppVerdict::STATUS_GUILTY) {
				$verdict->status = $verdict_status;

				$details['block_user'] = $details_block_user;

				// Flag the app
				$app->setToReported();
				$result = $result && $app->save();

				// TODO: generate verification entry
				$verif = new AppVerification;
				$verif->app_id = $app->id;
				$verif->base_changes_id = $app->version_id;
				$verif->verifier_id = $request->user()->id;
				$verif->concern = AppVerification::CONCERN_REPORT;
				$verif->status_id = 'unlisted';
				$verif->comment = $input_final_comments;
				/*$verif->details = [
					'_cause'		=> 'reported',
					'_verdict_id'	=> $verdict->id,
				];*/
				$result = $result && $verif->save();

				$verdict->verification_id = $verif->id;
			}

			$verdict->details = $details;

			$result = $result && $verdict->save();

			if($result) {
				// Update reports' status and add them into our verdict
				$input_reports = $request->input('report', []);
				foreach($input_reports as $irep) {
					$rep = $app->reports()->unresolved()->findOrFail($irep['id']);

					$repstatus = null;
					if($irep['status'] == 'valid') $repstatus = AppReport::STATUS_VALIDATED;
					elseif($irep['status'] == 'invalid') $repstatus = AppReport::STATUS_DROPPED;
					$rep->status = $repstatus;

					$rep->verdict_id = $verdict->id;
					$result = $result && $rep->save();
				}
			}

			if($result) {
				// Manage additional actions
				$owner = $app->owner;
				if($details_block_user && $owner) {
					// Only block if asked, and if not already blocked
					$owner->is_blocked = true;

					$ublock = new UserBlock;
					$ublock->user_id = $owner->id;
					$ublock->reason = __('admin/app_reports.messages.user_app_x_has_inappropriate_content_y', ['x' => $app->complete_name, 'y' => $verdict->comments]);
					$ublock->details = [
						'source'	=> 'verdict',
						'rel_id'	=> $verdict->id,
					];
					$result = $result && $ublock->save();
					$result = $result && $owner->save();
				}
			}

			// Done
		} catch(\Illuminate\Database\QueryException $e) {
			$result = FALSE;
			// TODO: do something with the message
			$error[] = $e->getMessage();
			// dd($e->getMessage());
		}

		if(!$result) {
			DB::rollback();

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

			return redirect()->route('admin.app_reports.verdicts', ['app' => $app->id, 'show_recent' => 1]);
		}
	}

	public function verdicts(Request $request, App $app)
	{
		$this->authorize('view-any', AppReport::class);
		//
		$data = [];

		$verdicts = $app->verdicts()->latest()->get();

		$data['app'] = $app;
		$data['verdicts'] = $verdicts;
		$data['show_recent'] = $request->input('show_recent', 0) == 1;

		return view('admin/app_report/verdicts', $data);
	}

}