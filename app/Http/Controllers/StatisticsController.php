<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppTag;
use App\Models\Prodi;

use App\DataManagers\AppManager;

use App\Rules\ModelExists;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{

	public function apps() {
		$data = [];


		// ========= Categories
		$categories = AppCategory::withCount(['apps' => function($query) {
			$query->isListed();
		}])->orderBy('apps_count', 'desc')->get()->transform(function($item) {
			$item->_id = $item->id;
			return $item;
		});
		$categories->keyBy('_id');
		$sum_categories = $categories->sum('apps_count');

		$cutoff = 5;
		$tmp_cat = new AppCategory;
		$tmp_cat->id = $tmp_cat->_id = '__others';
		$tmp_cat->cat_count = 0;
		$tmp_cat->apps_count = 0;
		for($i = $cutoff; $i < count($categories); $i++) {
			$tmp_cat->cat_count++;
			$tmp_cat->apps_count += $categories[$i]->apps_count;
		}
		$tmp_cat->name = __('frontend.statistics.apps.other_categories');

		$pie_categories = $categories->slice(0, $cutoff)->push($tmp_cat)->filter(function($item) {
			return $item->apps_count > 0;
		});

		$data['categories'] = $categories->push($tmp_cat)->filter(function($item) {
			return $item->apps_count > 0;
		})->map(function($item) use($sum_categories) {
			$item->percentage = round($item->apps_count / $sum_categories * 100, 1);
			return $item;
		});
		$data['pie_categories'] = $pie_categories;


		// ========= Tags
		$tags = AppTag::withCount(['apps' => function($query) {
			$query->isListed();
		}])->orderBy('apps_count', 'desc')->get()->transform(function($item) {
			$item->_id = $item->id;
			return $item;
		});
		$tags->keyBy('_id');
		$sum_tags = $tags->sum('apps_count');

		$cutoff = 10;
		$tmp_tag = new AppTag;
		$tmp_tag->id = $tmp_tag->_id = '__others';
		$tmp_tag->tag_count = 0;
		$tmp_tag->apps_count = 0;
		for($i = $cutoff; $i < count($tags); $i++) {
			$tmp_tag->tag_count++;
			$tmp_tag->apps_count += $tags[$i]->apps_count;
		}
		$tmp_tag->name = __('frontend.statistics.apps.other_tags');

		$pie_tags = $tags->slice(0, $cutoff)->push($tmp_tag)->filter(function($item) {
			return $item->apps_count > 0;
		});

		$data['tags'] = $tags->push($tmp_tag)->filter(function($item) {
			return $item->apps_count > 0;
		})->map(function($item) use($sum_tags) {
			$item->percentage = round($item->apps_count / $sum_tags * 100, 1);
			return $item;
		});
		$data['pie_tags'] = $pie_tags;


		// ========= Prodi
		// Different case here since we need to account for prodi-less apps as well
		$query = App::query()->withoutGlobalScopes();
		$query->isListed();
		$query->leftJoin('users as o', 'owner_id', '=', 'o.id');
		$query->leftJoin('ref_prodi as p', 'o.prodi_id', '=', 'p.id');

		$query->selectRaw('ifnull(p.id, "__none") as `pid`');
		$query->selectRaw('count(distinct apps.id) as `apps_count`');

		$query->groupBy('pid');
		$query->orderBy('apps_count', 'desc');
		$app_prodi = $query->get();
		$app_prodi->keyBy('pid');

		$prodi = Prodi::all()->transform(function($item) {
			$item->_id = $item->id;
			return $item;
		});

		$none_prodi = new Prodi;
		$none_prodi->id = $none_prodi->_id = '__none';
		$none_prodi->name = __('frontend.statistics.apps._none_prodi');
		$none_prodi->apps_count = 0;

		$prodi = $prodi->push($none_prodi)->keyBy('_id');
		$sum_prodi = $app_prodi->sum('apps_count');

		// Populate the apps count
		foreach($app_prodi as $item) {
			if(isset($prodi[$item->pid])) {
				$prodi[$item->pid]->apps_count += $item->apps_count;
			}
		}
		$prodi = $prodi->sortByDesc('apps_count');

		$cutoff = 5;
		$tmp_prodi = new Prodi;
		$tmp_prodi->id = $tmp_prodi->_id = '__others';
		$tmp_prodi->prodi_count = 0;
		$tmp_prodi->apps_count = 0;
		for($i = $cutoff; $i < count($prodi); $i++) {
			$tmp_prodi->prodi_count++;
			$tmp_prodi->apps_count += $prodi[$i]->apps_count;
		}
		$tmp_prodi->name = __('frontend.statistics.apps.other_prodi');

		$pie_prodis = $prodi->slice(0, $cutoff)->push($tmp_prodi)->filter(function($item) {
			return $item->apps_count > 0;
		});

		$data['prodis'] = $prodi->push($tmp_prodi)->filter(function($item) {
			return $item->apps_count > 0;
		})->map(function($item) use($sum_prodi) {
			$item->percentage = round($item->apps_count / $sum_prodi * 100, 1);
			return $item;
		});
		$data['pie_prodis'] = $pie_prodis;


		return view('statistics.apps', $data);
	}

}