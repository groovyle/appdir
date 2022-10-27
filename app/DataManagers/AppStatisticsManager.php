<?php

namespace App\DataManagers;

use App\Models\App;
use App\Models\AppReportCategory;

use App\DataManagers\StatsPeriod;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class AppStatisticsManager {

	public static function parseFilters($filters = []) {
		$opt_filters = optional($filters);

		if(isset($filters['date_range'])) {
			$parts = array_map('trim', explode(':', $filters['date_range']));
			$filters['date_start'] = Carbon::createFromFormat('d-m-Y', $parts[0])->format('Y-m-d');
			$filters['date_end'] = Carbon::createFromFormat('d-m-Y', $parts[1])->format('Y-m-d');
		} else {
			$filters['date_start'] = isset($filters['date_start'])
				? Carbon::createFromFormat('d-m-Y', $filters['date_start'])->format('Y-m-d')
				: null
			;
			$filters['date_end'] = isset($filters['date_end'])
				? Carbon::createFromFormat('d-m-Y', $filters['date_end'])->format('Y-m-d')
				: null
			;
		}

		$filters['group_mode'] = in_array($opt_filters['group_mode'], ['month', 'year']) ? $filters['group_mode'] : 'month';

		return $filters;
	}

	public static function applyDateFilters($query, $filters, $column) {
		if(!empty($filters['date_start']))
			$query->where($column, '>=', $filters['date_start']);
		if(!empty($filters['date_end']))
			$query->where($column, '<=', $filters['date_end']);

		$query->selectRaw($column.' as `at`');
		$query->selectRaw('date_format('.$column.', "%Y%m") as `ym`');
		$query->selectRaw('year('.$column.') as `year`');
		$query->selectRaw('month('.$column.') as `month`');
		if($filters['group_mode'] == 'year') {
			$query->groupBy('year');
			$query->orderBy('year');
		} else {
			$query->groupBy('ym');
			$query->orderBy('ym');
		}
	}

	public static function generatePeriods($items, $filters, $sub_items = false) {
		$filters = static::parseFilters($filters);

		// Generate each group even though it may be empty, so that listings
		// will always show sequential groups instead of being patchy.
		$start = $filters['date_start'] ?? $items->first()['at'] ?? $filters['date_end'] ?? null;
		$end = $filters['date_end'] ?? $items->last()['at'] ?? $filters['date_start'] ?? null;

		$groups = collect();
		$group_mode = $filters['group_mode'];
		if($start && $end) {
			$start = Carbon::createFromFormat('Y-m-d', $start);
			$end = Carbon::createFromFormat('Y-m-d', $end);
			if($group_mode == 'year') {
				$increment = '+1 year';
				$_gf = 'Y';
				$_tf = 'Y';
				$_stf = '\'y';
			} else {
				$increment = '+1 month';
				$_gf = 'Ym';
				$_tf = 'F Y';
				$_stf = 'M \'y';
			}

			for($tmp = clone $start; $tmp->format($_gf) <= $end->format($_gf); $tmp->add($increment)) {
				$item = new StatsPeriod;
				$item->group = $tmp->format($_gf);
				$item->group_object = $tmp2 = clone $tmp;
				$item->group_text = $tmp2->translatedFormat($_tf);
				$item->group_short_text = $tmp2->translatedFormat($_stf);
				if($sub_items) {
					$item->items = collect();
				}

				$groups[$item->group] = $item;
			}
		}

		foreach($items as $item) {
			if($group_mode == 'year') {
				$group = $item->year;
			} elseif($group_mode == 'month') {
				$group = $item->ym;
			}

			if(isset($groups[$group])) {
				if(!$sub_items) {
					foreach(get_object_vars($item) as $key => $value) {
						$groups[$group]->$key = $value;
					}
				} else {
					if(is_bool($sub_items))
						$groups[$group]->items[] = $item;
					else
						$groups[$group]->items[$item->$sub_items] = $item;
				}
			}
		}

		return $groups;
	}

	public static function newApps($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('apps as a');

		static::applyDateFilters($query, $filters, 'a.created_at');

		$query->selectRaw('count(distinct a.id) as `total`');

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		return $items;
	}

	public static function verifiedApps($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('apps as a');
		$query->leftJoin('app_verifications as av', function($query) {
			$query->on('a.id', '=', 'av.app_id');
			$query->where('av.status_id', 'approved');
		});
		$query->leftJoin('app_verifications as av2', function($query) {
			$query->on('a.id', '=', 'av2.app_id');
			$query->where('av2.status_id', 'approved');
			$query->on('av2.updated_at', '<=', 'av.updated_at');
			$query->on('av2.id', '<', 'av.id');
		});
		$query->whereNull('av2.id'); // where no prev approved verifs (i.e gets only the first approved verif)

		static::applyDateFilters($query, $filters, 'av.updated_at');

		$query->selectRaw('count(distinct if(av.id is not null, a.id, null)) as `total`');

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		return $items;
	}

	public static function edits($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('app_changelogs as ac');
		$query->leftJoin('app_changelogs as ac2', function($query) {
			$query->on('ac.app_id', '=', 'ac2.app_id');
			$query->on('ac2.id', '<', 'ac.id');
		});
		$query->whereNotNull('ac2.id'); // where not the first version of every app

		static::applyDateFilters($query, $filters, 'ac.created_at');

		$query->selectRaw('count(distinct ac.id) as `total`');

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		return $items;
	}

	public static function changesStatuses($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('app_changelogs as ac');
		$query->leftJoin('app_changelogs as ac2', function($query) {
			$query->on('ac.app_id', '=', 'ac2.app_id');
			$query->on('ac2.id', '<', 'ac.id');
		});
		$query->whereNotNull('ac2.id'); // where not the first version of every app

		static::applyDateFilters($query, $filters, 'ac.updated_at');

		$query->selectRaw('count(distinct if(ac.status in(?, ?), ac.id, null)) as `total_approved`', ['approved', 'committed']);
		$query->selectRaw('count(distinct if(ac.status = ?, ac.id, null)) as `total_rejected`', ['rejected']);
		$query->selectRaw('count(distinct if(ac.status = ?, ac.id, null)) as `total_pending`', ['pending']);

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		return $items;
	}

	public static function reports($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('apps as a');
		$query->join('app_reports as ar', 'a.id', '=', 'ar.app_id');

		static::applyDateFilters($query, $filters, 'ar.created_at');

		$query->selectRaw('count(distinct ar.id) as `total`');
		$query->selectRaw('count(distinct a.id) as `total_apps`');

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		return $items;
	}

	public static function reportCategories($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('app_reports as ar');
		$query->join('app_report_categories as arc', 'ar.id', '=', 'arc.report_id');
		$query->join('ref_app_report_categories as rarc', 'arc.category_id', '=', 'rarc.id');

		$query->select('rarc.*');

		static::applyDateFilters($query, $filters, 'ar.created_at');

		$query->selectRaw('count(distinct ar.id) as `total`');

		$query->groupBy('rarc.id');

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters, 'id');

		// Gather up categories as necessary
		$categories = AppReportCategory::all();
		foreach($categories as $cat) {
			$cat->total = 0;
			$cat->items = collect();
			foreach($items as $key => $period) {
				$period = clone $period;
				$cat->items[$key] = $period;
				$period->total = 0;
				if(isset($period->items[$cat->id])) {
					$period->total += $period->items[$cat->id]->total;
				}
				$cat->total += $period->total;
			}
		}

		return $categories;
	}

	public static function reportsStatuses($filters = [], $query_only = false) {
		$filters = static::parseFilters($filters);

		$query = DB::query();
		$query->from('app_reports as ar');
		$query->leftJoin('app_verdicts as avd', 'ar.id', '=', 'avd.id');

		static::applyDateFilters($query, $filters, 'ar.created_at');

		$query->selectRaw('count(distinct if(ar.status = ?, ar.id, null)) as `total_unresolved`', ['submitted']);
		$query->selectRaw('count(distinct if(ar.status = ?, ar.id, null)) as `total_valid`', ['validated']);
		$query->selectRaw('count(distinct if(ar.status = ?, ar.id, null)) as `total_invalid`', ['dropped']);

		if($query_only) {
			return $query;
		}

		$items = $query->get();
		$items = static::generatePeriods($items, $filters);

		// Gather up statuses
		$statuses = collect();

		$tmp = new StatsPeriod;
		$tmp->id = 'valid';
		$tmp->name = __('admin/stats.app_activities.report_valid');
		$statuses[] = $tmp;

		$tmp = new StatsPeriod;
		$tmp->id = 'invalid';
		$tmp->name = __('admin/stats.app_activities.report_invalid');
		$statuses[] = $tmp;

		$tmp = new StatsPeriod;
		$tmp->id = 'unresolved';
		$tmp->name = __('admin/stats.app_activities.report_unresolved');
		$statuses[] = $tmp;

		foreach($statuses as $st) {
			$st->total = 0;
			$st->items = collect();
			foreach($items as $key => $period) {
				$period = clone $period;
				$st->items[$key] = $period;
				$st->total += $period->{'total_'.$st->id};
			}
		}

		return $statuses;
	}

}