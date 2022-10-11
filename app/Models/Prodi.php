<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prodi extends Model
{
	use SoftDeletes;
	use Concerns\LoggedActions;
	//
	protected $table = 'ref_prodi';

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

	public function users() {
		return $this->hasMany('App\User', 'prodi_id');
	}

	public function getCompactNameAttribute() {
		return $this->attributes['short_name'] ?? $this->attributes['name'] ?? null;
	}

	public function getCompleteNameAttribute() {
		if(!isset($this->attributes['name']))
			return null;

		$complete = $this->attributes['name'];
		if($short_name = $this->attributes['short_name'])
			$complete .= ' ('.$short_name.')';
		return $complete;
	}
}
