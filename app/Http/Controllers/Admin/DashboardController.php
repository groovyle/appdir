<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DataManagers\AppManager;
use App\DataManagers\AppStatisticsManager as StatMan;
use App\DataManagers\UserManager;

class DashboardController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		$role = UserManager::userHighestRole();
		$method = 'dashboard'.ucfirst(strtolower($role));
		if(method_exists($this, $method))
			return $this->$method();

		return view('admin/dashboard/_default');
	}

	public function dashboardSuperadmin()
	{
		$filters = ['_no_dates' => true];

		$total_apps = \App\Models\App::count();
		$total_public_apps = \App\Models\App::query()->isListed()->count();
		$total_new_apps = optional(StatMan::newApps($filters)->first())->total;
		$total_verified_apps = optional(StatMan::verifiedApps($filters)->first())->total;

		$total_unverifs = get_count_from_list_query(AppVerificationController::listQuery()['query'], 'a.id');
		$reports = optional(StatMan::reports($filters)->first());
		$total_reports = $reports->total;
		$total_apps_reported = $reports->total_apps;

		$query_users = \App\User::query()->regular();
		$view_mode = '';
		UserManager::scopeListQuery($query_users, $view_mode);

		$total_users = $query_users->count();
		$total_blocked_users = (clone $query_users)->blocked()->count();

		$total_prodis = \App\Models\Prodi::count();

		$total_categories = \App\Models\AppCategory::count();
		$total_tags = \App\Models\AppTag::count();

		$app_activities_query = AppController::activityLogQuery()['query'];
		$app_activities = $app_activities_query->limit(5)->get();
		AppController::activityLogPrepareItems($app_activities);

		$data = compact(
			'total_apps',
			'total_public_apps',
			'total_new_apps',
			'total_verified_apps',
			'total_unverifs',
			'total_reports',
			'total_apps_reported',
			'total_users',
			'total_blocked_users',
			'total_prodis',
			'total_categories',
			'total_tags',
			'app_activities',
		);

		return view('admin/dashboard/superadmin', $data);
	}

	public function dashboardAdmin()
	{
		$filters = ['_no_dates' => true];

		$total_apps = \App\Models\App::count();
		$total_public_apps = \App\Models\App::query()->isListed()->count();
		$total_new_apps = optional(StatMan::newApps($filters)->first())->total;
		$total_verified_apps = optional(StatMan::verifiedApps($filters)->first())->total;

		$total_unverifs = get_count_from_list_query(AppVerificationController::listQuery()['query'], 'a.id');
		$reports = optional(StatMan::reports($filters)->first());
		$total_reports = $reports->total;
		$total_apps_reported = $reports->total_apps;

		$query_users = \App\User::query()->regular();
		$view_mode = '';
		UserManager::scopeListQuery($query_users, $view_mode);

		$total_users = $query_users->count();
		$total_blocked_users = (clone $query_users)->blocked()->count();

		$total_categories = \App\Models\AppCategory::count();
		$total_tags = \App\Models\AppTag::count();

		$app_activities_query = AppController::activityLogQuery()['query'];
		$app_activities = $app_activities_query->limit(5)->get();
		AppController::activityLogPrepareItems($app_activities);

		$data = compact(
			'total_apps',
			'total_public_apps',
			'total_new_apps',
			'total_verified_apps',
			'total_unverifs',
			'total_reports',
			'total_apps_reported',
			'total_users',
			'total_blocked_users',
			'total_categories',
			'total_tags',
			'app_activities',
		);

		return view('admin/dashboard/admin', $data);
	}

	public function dashboardMahasiswa()
	{
		$filters = ['_no_dates' => true];

		$query_apps = StatMan::newApps($filters, true);
		$total_apps = $query_apps->count();

		$query_public = clone $query_apps;
		$query_public->where('is_verified', 1);
		$query_public->where('is_published', 1);
		$query_public->where('is_reported', 0);
		$query_public->where('is_private', 0);
		$query_public->where('o.is_blocked', 0);
		$total_public_apps = $query_public->count();

		$query_changes = StatMan::changesStatuses($filters, true, null, false);
		$changes = optional($query_changes->first());
		$total_changes_approved = $changes->total_approved ?? 0;
		$total_changes_pending = $changes->total_pending ?? 0;

		$reports = optional(StatMan::reports($filters)->first());
		$total_apps_reported = $reports->total_apps;

		$app_activities_query = AppController::activityLogQuery()['query'];
		$app_activities = $app_activities_query->limit(5)->get();
		AppController::activityLogPrepareItems($app_activities);

		$data = compact(
			'total_apps',
			'total_public_apps',
			'total_changes_approved',
			'total_changes_pending',
			'total_apps_reported',
			'app_activities',
		);

		return view('admin/dashboard/mahasiswa', $data);
	}
}
