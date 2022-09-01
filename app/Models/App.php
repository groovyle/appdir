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
		// 'verifications',
		// 'verifications.status',
		// 'last_verification',
		// 'last_verification.status',
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

	public static function getFrontendItem($slug) {
		$item = null;
		if(is_numeric($slug))
			$item = static::frontend()->whereKey($slug)->first();
		if(!$item)
			$item = static::frontend()->where('slug', $slug)->firstOrFail();

		return $item;
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
		return $this->hasMany('App\Models\AppVerification', 'app_id')
			->orderBy(AppVerification::UPDATED_AT)
		;
	}

	public function admin_verifications() {
		return $this->verifications()->whereHas('status', function($query) {
			$query->where('by', 'verifier');
		});
	}

	public function last_verification() {
		return $this->hasOne('App\Models\AppVerification', 'app_id')->latest(AppVerification::UPDATED_AT);
	}

	public function latest_approved_verifications() {
		// Get latest sequential approvals
		return $this->hasMany('App\Models\AppVerification', 'app_id')->latestSequence('approved');
	}

	public function changelogs() {
		return $this->hasMany('App\Models\AppChangelog', 'app_id');
	}

	public function pending_changes() {
		$query = $this->changelogs()->pending();
		$query = $this->_future_changes($query);
		return $query;
	}

	public function approved_changes() {
		$verif_ids = $this->latest_approved_verifications->modelKeys();
		$query = $this->changelogs()->inVerifIds($verif_ids);
		$query = $this->_future_changes($query);
		return $query;
	}

	public function floating_changes() {
		$query = $this->changelogs()->floating();
		$query = $this->_future_changes($query);
		return $query;
	}

	protected function _future_changes($query) {
		// $query = $this->changelogs() or similar
		if($this->version) {
			$query->where('created_at', '>=', (string) $this->version->created_at)
				->where('id', '>', $this->version->id)
			;
		}
		$query->orderBy('created_at');
		$query->orderBy('version');

		return $query;
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
		return optional($this->version)->version;
	}

	public function getVerificationStatusAttribute() {
		if($this->verifications()->exists()) {
			return $this->last_verification->status;
		} else {
			return VerificationStatus::getDefault();
		}
	}

	public function getHasHistoryAttribute() {
		return $this->changelogs()->count() > 1;
	}

	public function getHasAdminVerificationsAttribute() {
		return $this->admin_verifications()->exists();
	}

	public function getHasPendingChangesAttribute() {
		return $this->pending_changes()->exists();
	}

	public function getHasApprovedChangesAttribute() {
		return $this->approved_changes()->exists();
	}

	public function getHasFloatingChangesAttribute() {
		return $this->floating_changes()->exists();
	}

	public function getIsUnverifiedNewAttribute() {
		// TODO: maybe check if it has any verifications as well?
		return !$this->is_verified && $this->changelogs()->count() < 2;
	}

	public function getCompleteNameAttribute() {
		return $this->name . ($this->short_name ? ' ('.$this->short_name.')' : '');
	}

	public function getPublicNameAttribute() {
		return $this->short_name ?: $this->name;
	}

	public function getPublicUrlAttribute() {
		// TODO: decide whether to use slug or ID for the public URL
		// ID	= weird number
		// Slug	= nicer, but changes with the name, so can't really be bookmarked
		return $this->get_public_url($this->id);
	}

	public function get_public_url($slug, $params = []) {
		return route('apps.page', array_merge(['slug' => $slug], $params));
	}
}
