<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppType extends Model
{
	use SoftDeletes;
	//
	protected $table = 'ref_app_types';

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot() {
		parent::boot();

		/*static::addGlobalScope('orderName', function (Builder $builder) {
			$builder->orderBy('name', 'asc');
		});*/
	}

	/*public function getNameAttribute($value) {
		return trans($value);
	}*/
}
