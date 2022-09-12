<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppReportCategory extends Model
{
	use SoftDeletes;
	//
	protected $table = 'ref_app_report_categories';

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		// Can't sort by name because they are translated later during rendering
		/*static::addGlobalScope('orderName', function (Builder $builder) {
			$builder->orderBy('name', 'asc');
		});*/
		static::addGlobalScope('order', function (Builder $builder) {
			$builder->orderBy('order', 'asc');
		});
	}

	public function reports() {
		return $this->belongsToMany('App\Models\AppReport', 'app_report_categories', 'category_id', 'report_id');
	}

	public function getNameAttribute() {
		return $this->getTranslation($this->attributes['name']);
	}

	public function getDescriptionAttribute() {
		return $this->getTranslation($this->attributes['description']);
	}

	protected function getTranslation($value) {
		$key = 'common.app_report_categories.'.$value;
		return \Lang::has($key) ? \Lang::get($key) : $value;
	}

	public function __toString() {
		return $this->name;
	}

}
