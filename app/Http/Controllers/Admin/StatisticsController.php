<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\StatsAppActivities;
use App\User;

use App\DataManagers\AppStatisticsManager as StatMan;

use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class StatisticsController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function app_activities()
	{
		$this->authorize('view', StatsAppActivities::class);

		// Defaults to 3 months back
		$default_date_start = Carbon::parse('first day of 2 months ago')->format('d-m-Y');
		$default_date_end = Carbon::parse('last day of this month')->format('d-m-Y');

		$filters = get_filters(['date_range', 'group_mode'], [
			'date_range'	=> $default_date_start.' : '.$default_date_end,
			'group_mode'	=> 'month',
		]);


		$time_groups = StatMan::generatePeriods([], $filters);
		$new_apps = StatMan::newApps($filters);
		$verified_apps = StatMan::verifiedApps($filters);

		$edits = StatMan::edits($filters);
		$changes_statuses = StatMan::changesStatuses($filters);

		$reports = StatMan::reports($filters);

		$report_categories = StatMan::reportCategories($filters);
		$report_statuses = StatMan::reportsStatuses($filters);


		$data = compact(
			'time_groups',
			'new_apps',
			'verified_apps',
			'edits',
			'changes_statuses',
			'reports',
			'report_categories',
			'report_statuses'
		);
		$data['filters'] = optional($filters);

		return view('admin/statistics/app_activities', $data);
	}

	public function topical() {
		// by category

		// by tag

		// by private (or published/not yet published?)

		//

		// by prodi (all access only/superadmins maybe)
	}

}
