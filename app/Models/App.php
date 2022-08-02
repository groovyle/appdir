<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class App extends Model
{
	use SoftDeletes;
	use Concerns\HasCudActors;
	use Concerns\HasFilteredAttributes;

	const CREATED_BY = NULL;

	protected $attributes = [
		'is_verified' => false,
	];

	protected $guarded = [
		'is_verified',
	];

	protected $casts = [
		'is_verified' => 'boolean',
	];

	protected $with = [
		'owner',
		'verifications',
		'verifications.status',
		'last_verification',
		'last_verification.status',
	];

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		/*static::addGlobalScope('order_verification',
			function (Builder $builder) {
				$self = __CLASS__;
				$app = new $self;
				$table = $app->getTable();
				$updated_at = $app->getQualifiedUpdatedAtColumn();
				$pkey = $app->getQualifiedKeyName();

				$ver = (new AppVerification);
				$ver_table = $ver->getTable();
				$ver_updated_at = $ver->getQualifiedUpdatedAtColumn();
				$ver_fk = $ver->getQualifiedAppColumn();

				$builder->select($table.'.*');
				$builder->leftJoin($ver_table, $pkey, '=', $ver_fk);
				$builder->orderByRaw(sprintf('COALESCE(%s, %s) DESC', $ver_updated_at, $updated_at));
				$builder->groupBy($pkey);
			}
		);*/
		static::addGlobalScope('order_verification', function (Builder $builder) {
			$builder->latest(static::UPDATED_AT);
		});
	}

	public function scopeFrontend($query) {
		$query->without([
			'verifications',
			'verifications.status',
		]);
		$query->with([
			'visuals',
			'visual',
		]);
		$query->withCount([
			'visuals',
		]);

		$query->where('is_verified', 1);
	}

	public function scopeFrontendItem($query, $slug) {
		$query->frontend();
		return $query->where('slug', $slug)->firstOrFail();
	}

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
		// Overwrite this function so that route bindings will filter based on the
		// qualified key name ("table.key") instead of a bare column name ("key").
		// This is done so that scopes which introduces JOINs to tables that have
		// the same key name (e.g "id") will not break our application.

		// Taken from Illuminate\Database\Eloquent\Model::getRouteKeyName().

		// return $this->getKeyName(); // -> breaks
		return $this->getQualifiedKeyName();
	}

	public function logo() {
		return $this->hasOne('App\Models\AppLogo');
	}

	public function thumbnail() {
		// TODO
		return $this->visual();
	}
	// Alias
	public function visual() {
		// TODO
		// return $this->hasOne('App\Models\AppVisualMedia')->oldest();
		return $this->hasOne('App\Models\AppVisualMedia')->orderBy('order', 'asc');
	}

	public function visuals() {
		return $this->hasMany('App\Models\AppVisualMedia', 'app_id');
	}
	// Alias
	public function visual_media() {
		return $this->visuals();
	}

	public function categories() {
		return $this->belongsToMany('App\Models\AppCategory', 'app_categories', 'app_id', 'category_id');
	}

	public function tags() {
		return $this->belongsToMany('App\Models\AppTag', 'app_tags', 'app_id', 'tag');
	}

	public function type() {
		return $this->belongsTo('App\Models\AppType');
	}

	public function owner() {
		return $this->belongsTo('App\User', 'owner_id');
	}

	public function verifications() {
		return $this->hasMany('App\Models\AppVerification', 'app_id');
	}

	public function last_verification() {
		return $this->hasOne('App\Models\AppVerification', 'app_id')->latest(AppVerification::UPDATED_AT);
	}

	public function changelogs() {
		return $this->hasMany('App\Models\AppChangelog', 'app_id');
	}

	public function version() {
		return $this->belongsTo('App\Models\AppChangelog', 'version_id');
	}

	public function last_changes() {
		return $this->hasOne('App\Model\AppChangelog', 'app_id')->latest(AppVerification::CREATED_AT);
	}

	public function lastVersionNumber() {
		return ! $this->changelogs()->exists() ? 0 : $this->changelogs()->max('version');
	}

	public function nextVersionNumber() {
		return $this->lastVersionNumber() + 1;
	}

	public function getVersionNumberAttribute() {
		return $this->version()->exists() ? $this->version->version : NULL;
	}

	public function getFullDirectoryAttribute() {
		// TODO: DUMMY
		return $this->owner->home_directory . $this->directory;
	}

	public function getFullUrlAttribute() {
		return url_auto_scheme($this->url);
	}

	public function getVerificationStatusAttribute() {
		if($this->verifications()->exists()) {
			return $this->last_verification->status;
		} else {
			return VerificationStatus::getDefault();
		}
	}
}
