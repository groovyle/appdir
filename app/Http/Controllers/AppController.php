<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppTag;
use App\Models\AppReport;
use App\Models\AppReportCategory;
use App\Models\SystemUsers\Guest;

use App\DataManagers\AppManager;

use App\Rules\GoogleRecaptchaV2;
use App\Rules\ModelExists;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AppController extends Controller
{
	//

	public function index() {

		$data = [];

		$query = App::frontend();
		$query->with(['thumbnail']);
		$filter_count = 0;
		$filters = request(['s', 'c', 't']);
		$total_all = $query->count();

		if($search = trim(request('s'))) {
			$str = escape_mysql_like_str($search);
			$like = '%'.$str.'%';
			$query->where(function($query) use($like) {
				$query->where('name', 'like', $like);
				$query->orWhere('short_name', 'like', $like);
				$query->orWhere('description', 'like', $like);
			});

			$filter_count++;
		}

		if($categories = request('c')) {
			if(!is_array($categories))
				$categories = explode(',', $categories);

			$categories = array_unique(array_filter($categories));
			$query->whereHas('categories', function($query) use($categories) {
				$query->whereIn('id', $categories);
			});

			$filter_count++;
		}

		if($tags = request('t')) {
			if(!is_array($tags))
				$tags = explode(',', $tags);

			$tags = array_unique(array_filter($tags));
			$query->whereHas('tags', function($query) use($tags) {
				$query->whereIn('name', $tags);
			});

			$filter_count++;
		}

		$query->orderBy('name');
		$query->orderBy('short_name');

		// $total_search = $query->count();
		$per_page = settings('app.listing.per_page', 20);
		$apps = $query->paginate($per_page);
		$apps->appends($filters);

		$data['total_all'] = $total_all;
		// $data['total_search'] = $total_search;
		$data['apps'] = $apps;
		$data['categories'] = AppCategory::all();
		$data['tags'] = AppTag::all();
		$data['filter_count'] = $filter_count;
		$data['show_filter'] = $filter_count > 0 || request()->has('show_filter');

		return view('app/index', $data);
	}

	public function page(string $slug) {
		// If user is admin/verifier/owner, allow viewing an unpublished app
		// as a preview

		$app = App::getFrontendItem($slug, false, false);
		$ori = $app;
		// $this->authorize('view-public', $app ?? App::class);
		$check = Gate::inspect('view-public', $app ? [$app, true] : [App::class, null, true]);
		if(!$check->allowed()) {
			throw (new \Illuminate\Database\Eloquent\ModelNotFoundException)->setModel(App::class);
			return;
		}
		// TODO: custom 403 or 404 page for app page

		$check_data = $check->message();
		if($check_data['view_mode'] == 'admin') {
			$app->load(['changelogs']);

			// Can't view a rejected version... or can we?
			$app->setRelation('changelogs',
				$app->changelogs->keyBy('version')->filter(function($item, $key) {
					// return !$item->is_rejected;
					return true;
				})
			);

			$input_version = request('version');
			$version = $app->changelogs[$input_version] ?? null;
			if($input_version && $version && $version->version != $app->version_number) {
				// Mock item
				$app = AppManager::getMockItem($app->id, $version->version);
			}
		}

		if($app->is_original_version) {
			// Got through, can access

			// Only increase page views if it's publicly viewing...?
			if($ori->owner && !$ori->owner->is_me && $app->is_listed) {
				$app->increasePageViews();
			}
		}

		$data = [];
		$data['ori'] = $ori;
		$data['app'] = $app;

		$data['view_mode'] = $check_data['view_mode'];

		$data['report_categories'] = AppReportCategory::all();
		$data['report_reason_limit'] = settings('app.reports.reason_limit', 500);
		$data['page_slug'] = $slug;

		return view('app/page', $data);
	}

	public function preview(string $slug) {
		$app = App::getFrontendItem($slug);

		$data = [];
		$data['app'] = $app;

		return view('app/preview', $data);
	}

	public function postReport(Request $request, string $slug) {

		$app = App::getFrontendItem($slug, true, false);

		$logged_in = Auth::check() && Auth::user()->is_verified;
		$user = Auth::user();
		$user_id = optional($user)->id;
		$version_id = optional($app->version)->id;

		$result = true;
		$error = [];

		// Reporter check, check for app reports in the current version
		$email = $request->report_email;
		if($logged_in || !empty($email)) {
			$query = AppReport::where([
				'app_id'		=> $app->id,
				'version_id'	=> $version_id,
			]);
			if($logged_in) {
				$query->where('user_id', $user_id);
			} else {
				$query->where('email', $email);
			}

			// NOTE: no unresolved checks, consider the following:
			// A user reported, but then it got dropped because it's false. The user
			// won't be able to report again if the unresolved check is ignored, and
			// it should stay this way.
			// $query->unresolved();

			$existing_report = $query->first();
			if($existing_report) {
				$msg = __('frontend.apps.message.you_have_reported_this_app_in_the_current_version_x', ['x' => $app->version_number]);
				// $result = false;
				// $error[] = $msg;

				// Pass a message
				$request->session()->flash('report_message', [
					'message'	=> $msg,
					'type'		=> 'warning'
				]);
				// $request->session()->flash('report_existing', $existing_report);
				return redirect()->route('apps.page', ['slug' => $app->slug]);
			}
		}

		// Validation
		if($result) {
			request_replace_nl($request);
			$request->merge(['is_report_form' => 1]);
			// dd($request->all());

			$rules = [
				// 'dummy'					=> ['required'], // dummy field for validation
				'report_email'			=> [],
				'report_categories'		=> ['required', 'array'],
				'report_categories.*'	=> ['integer', new ModelExists(AppReportCategory::class)],
				'report_reason'			=> ['required', 'string', 'min:50'],
				'g-recaptcha-response'	=> ['required', new GoogleRecaptchaV2],
			];
			if(!$logged_in) {
				$rules['report_email'] = ['required', 'email'];
			}

			if($reason_limit = settings('app.reports.reason_limit', 500))
				$rules['report_reason'][] = 'max:'.$reason_limit;

			$field_names = [
				'report_email'			=> __('frontend.apps.fields.email'),
				'report_categories'		=> __('frontend.apps.fields.report_categories'),
				'report_categories.*'	=> __('frontend.apps.fields.report_category'),
				'report_reason'			=> __('frontend.apps.fields.report_reason'),
			];

			// Validate
			$validData = $request->validate($rules, [], $field_names);

			// dd($request->all());
		}

		DB::beginTransaction();
		if($result) {
			try {
				// Create new report item
				$report = new AppReport;
				$report->app_id = $app->id;
				$report->version_id = $version_id;
				if($logged_in) {
					$report->user_id = $user->id;
				} else {
					$report->email = $request->report_email;
					$report->setActionsActor(Guest::instance());
				}
				$report->reason = $request->report_reason;
				$result = $report->save();

				// Report categories
				if($result) {
					$report->categories()->attach($request->report_categories);
				}
			} catch(\Illuminate\Database\QueryException $e) {
				$result = FALSE;
				$error[] = $e->getMessage();
			}
		}

		if(!$result) {
			DB::rollback();

			return redirect()->back()->withInput()->withErrors($error);
		} else {
			DB::commit();

			// Pass a message
			$request->session()->flash('report_message', [
				'message'	=> __('frontend.apps.message.report_submitted'),
				'type'		=> 'success'
			]);

			return redirect()->route('apps.page', ['slug' => $app->slug]);
		}
	}

}
