<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppCategory extends Model
{
	use SoftDeletes;
	//
	protected $table = 'ref_app_categories';

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		static::addGlobalScope('orderName', function (Builder $builder) {
			$builder->orderBy('name', 'asc');
		});
	}

	public function apps() {
		return $this->belongsToMany('App\Models\App', 'app_categories', 'category_id', 'app_id');
	}

	public function __toString() {
		return $this->name;
	}

}
