<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppTag extends Model
{
	use SoftDeletes, Concerns\HasCudActors {
		Concerns\HasCudActors::runSoftDelete insteadof SoftDeletes;
	}

	//
	protected $table = 'ref_app_tags';
	protected $primaryKey = 'name';
	protected $keyType = 'string';
	public $incrementing = FALSE;

	protected static $unguarded = true;

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
		static::addGlobalScope('withTrashed', function (Builder $builder) {
			$builder->withTrashed();
		});
	}

	// Alias
	public function creator() {
		return $this->createdBy();
	}

	public function getIsCustomAttribute() {
		// Whether the tag was made by a user, i.e a custom tag
		return $this->has('creator');
	}

	public function apps() {
		return $this->belongsToMany('App\Models\App', 'app_tags', 'tag', 'app_id');
	}

	public function __toString() {
		return $this->name;
	}
}
