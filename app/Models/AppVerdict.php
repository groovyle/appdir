<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVerdict extends Model
{
	protected $table = 'app_verdicts';

	use SoftDeletes,
		Concerns\HasCudActors;

	const STATUS_INNOCENT		= 'innocent';
	const STATUS_GUILTY			= 'guilty';

	protected $casts = [
		'details' => 'array',
	];

	protected $with = [
		'version',
		'updater',
		'reports.categories',
	];

	public function scopeInnocent($query) {
		$query->where('status', static::STATUS_INNOCENT);
	}

	public function scopeGuilty($query) {
		$query->where('status', static::STATUS_GUILTY);
	}

	public static function statusAll() {
		return [static::STATUS_INNOCENT, static::STATUS_GUILTY];
	}

	public function getIsInnocentAttribute() {
		return $this->attributes['status'] == static::STATUS_INNOCENT;
	}

	public function getIsGuiltyAttribute() {
		return $this->attributes['status'] == static::STATUS_GUILTY;
	}

	public function app() {
		return $this->belongsTo('App\Models\App', 'app_id');
	}

	public function version() {
		return $this->belongsTo('App\Models\AppChangelog', 'version_id');
	}

	public function reports() {
		return $this->hasMany('App\Models\AppReports', 'verdict_id');
	}

}