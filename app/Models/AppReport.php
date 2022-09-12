<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppReport extends Model
{
	protected $table = 'app_reports';

	use Concerns\HasCudActors;

	const CREATED_BY = null;
	const DELETED_BY = null;

	const STATUS_SUBMITTED		= 'submitted'; // for new submitted reports
	const STATUS_VALIDATED		= 'validated'; // for validated/accepted reports
	const STATUS_DROPPED		= 'dropped'; // for reports which were deemed invalid

	protected $casts = [
		'details' => 'array',
	];

	public function app() {
		return $this->belongsTo('App\Models\App', 'app_id');
	}

	public function version() {
		return $this->belongsTo('App\Models\AppChangelog', 'version_id');
	}

	public function user() {
		return $this->belongsTo('App\User', 'user_id');
	}

	public function categories() {
		return $this->belongsToMany('App\Models\AppReportCategory', 'app_report_categories', 'report_id', 'category_id');
	}

}