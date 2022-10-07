<?php

namespace App\Models;

use App\Models\AppReportCategory;

use App\DataManagers\AppReportManager;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppVerdict extends Model
{
	use SoftDeletes, Concerns\HasCudActors {
		Concerns\HasCudActors::runSoftDelete insteadof SoftDeletes;
	}

	protected $table = 'app_verdicts';

	const STATUS_INNOCENT		= 'innocent';
	const STATUS_GUILTY			= 'guilty';

	protected $casts = [
		'details' => 'array',
	];

	protected $with = [
		'version',
		'updatedBy',
	];

	public function __construct(array $attributes = []) {
		parent::__construct($attributes);

		$this->with['reports'] = function($query) {
			$query->defaultOrder();
		};
		$this->with[] = 'reports.categories'; // does putting $with stuff here work?
	}

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		static::registerModelEvent('retrieved', function($model) {
			$model->loadCompiledRelations();
		});
	}

	public function loadCompiledRelations() {
		$compiled_relations = AppReportManager::compileRelationsFromReports($this->reports);
		foreach($compiled_relations as $key => $rel) {
			$this->$key = $rel;
		}
	}

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
		return $this->hasMany('App\Models\AppReport', 'verdict_id');
	}

	public function verification() {
		return $this->belongsTo('App\Models\AppVerification', 'verification_id');
	}

	/*public function getCategoriesAttribute() {
		$report_ids = $this->reports->modelKeys();
		$categories = AppReportCategory::whereHas('reports', function($query) use($report_ids) {
			$query->where('report_id', $report_ids);
		})->get();
		return $categories;
	}*/

}