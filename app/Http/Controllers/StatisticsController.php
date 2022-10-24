<?php

namespace App\Http\Controllers;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppTag;

use App\DataManagers\AppManager;

use App\Rules\ModelExists;

use Illuminate\Http\Request;

class StatisticsController extends Controller
{

	public function apps() {
		$data = [];


		// ========= Categories
		$categories = AppCategory::withCount(['apps'])->orderBy('apps_count', 'desc')->get();
		$categories->keyBy('id');
		$sum_categories = $categories->sum('apps_count');

		$cutoff = 5;
		$tmp_cat = new AppCategory;
		$tmp_cat->id = '__others';
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
		// dd($pie_categories->toArray());

		$data['categories'] = $categories->push($tmp_cat)->filter(function($item) {
			return $item->apps_count > 0;
		})->map(function($item) use($sum_categories) {
			$item->percentage = round($item->apps_count / $sum_categories * 100, 1);
			return $item;
		});
		$data['pie_categories'] = $pie_categories;


		// ========= Tags
		$tags = AppTag::withCount(['apps'])->orderBy('apps_count', 'desc')->get();
		$tags->keyBy('id');
		$sum_tags = $tags->sum('apps_count');

		$cutoff = 5;
		$tmp_tag = new AppTag;
		$tmp_tag->id = '__others';
		$tmp_tag->cat_count = 0;
		$tmp_tag->apps_count = 0;
		for($i = $cutoff; $i < count($tags); $i++) {
			$tmp_tag->cat_count++;
			$tmp_tag->apps_count += $tags[$i]->apps_count;
		}
		$tmp_tag->name = __('frontend.statistics.apps.other_tags');

		$pie_tags = $tags->slice(0, $cutoff)->push($tmp_tag)->filter(function($item) {
			return $item->apps_count > 0;
		});
		// dd($pie_tags->toArray());

		$data['tags'] = $tags->push($tmp_tag)->filter(function($item) {
			return $item->apps_count > 0;
		})->map(function($item) use($sum_tags) {
			$item->percentage = round($item->apps_count / $sum_tags * 100, 1);
			return $item;
		});
		$data['pie_tags'] = $pie_tags;


		return view('statistics.apps', $data);
	}

}