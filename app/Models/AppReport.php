<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppReport extends Model
{
	protected $table = 'app_reports';

	use SoftDeletes,
		Concerns\HasCudActors;

	const CREATED_BY = null;

	const STATUS_SUBMITTED		= 'submitted'; // for new submitted reports
	const STATUS_VALIDATED		= 'validated'; // for validated/accepted reports
	const STATUS_DROPPED		= 'dropped'; // for reports which were deemed invalid

	protected $casts = [
		'details' => 'array',
	];

	protected $with = [
		'version',
		'user',
		'categories',
	];

	public function scopeUnresolved($query) {
		$query->whereIn('status', static::statusUnresolved());
	}

	public function scopeResolved($query) {
		$query->whereIn('status', static::statusResolved());
	}

	public function scopeRegistered($query, $invert = false) {
		if(!$invert) {
			$query->whereNotNull('user_id');
		} else {
			$query->whereNull('user_id');
		}
	}

	public static function statusUnresolved() {
		return [static::STATUS_SUBMITTED];
	}

	public static function statusResolved() {
		return [static::STATUS_VALIDATED, static::STATUS_DROPPED];
	}

	public function getIsUnresolvedAttribute() {
		return in_array($this->attributes['status'], static::statusUnresolved());
	}

	public function getIsResolvedAttribute() {
		return in_array($this->attributes['status'], static::statusResolved());
	}

	public function getRegisteredSenderAttribute() {
		return $this->attributes['user_id'] != null;
	}

	public function app() {
		return $this->belongsTo('App\Models\App', 'app_id');
	}

	public function version() {
		return $this->belongsTo('App\Models\AppChangelog', 'version_id');
	}

	public function verdict() {
		return $this->belongsTo('App\Models\AppVerdict', 'verdict_id');
	}

	public function user() {
		return $this->belongsTo('App\User', 'user_id');
	}

	public function categories() {
		return $this->belongsToMany('App\Models\AppReportCategory', 'app_report_categories', 'report_id', 'category_id');
	}

}