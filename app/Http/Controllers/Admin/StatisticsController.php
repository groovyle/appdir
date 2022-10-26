<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\App;
use App\Models\StatsAppActivities;
use App\User;

use Bouncer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function app_activities()
	{
		$this->authorize('view', StatsAppActivities::class);

		$data = [];

		// $month_filters

		// $new_apps_per_month

		// $verified_apps_per_month

		// $edits_per_month

		$data['filters'] = optional();

		return view('admin/statistics/app_activities', $data);
	}

}
