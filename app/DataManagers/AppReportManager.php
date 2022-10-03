<?php

namespace App\DataManagers;

use App\Models\App;
use App\Models\AppVerification;
use App\Models\AppChangelog;
use App\Models\AppReport;
use App\Models\AppReportCategory;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AppReportManager {

	public static function compileRelationsFromReports($reports) {
		// $reports should be an array/Collection of AppReport instances

		$categories = collect();
		$versions = collect();
		foreach($reports as $r) {
			foreach($r->categories as $rc) {
				if(!isset($categories[$rc->id])) {
					$rc->reports_count = 0;
					$rc->setRelation('reports', collect());
					$categories[$rc->id] = $rc;
				}
				$categories[$rc->id]->reports_count++;
				$categories[$rc->id]->reports->push($r);
			}

			$version_id = $r->version_id ?: '__none';
			if($r->version) {
				$v = $r->version;
				$v->display_name = __('admin/app_verifications.version_x', ['x' => $v->version]);
			} else {
				$v = new AppChangelog;
				$v->id = '__none';
				$v->version = '__none';
				$v->display_name = __('admin/app_reports.version__none');
				$r->setRelation('version', $v);
			}
			if(!isset($versions[$v->id])) {
				$v->reports_count = 0;
				$v->setRelation('reports', collect());
				$versions[$v->id] = $v;
			}
			$versions[$v->id]->reports_count++;
			$versions[$v->id]->reports->push($r);
		}

		$categories = $categories->sortBy('id')->sortBy('order');
		$versions = $versions->sortByDesc('version');

		return compact('categories', 'versions');
	}

}