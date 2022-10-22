<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class App extends Model
{
	use SoftDeletes, Concerns\HasCudActors {
		Concerns\HasCudActors::runSoftDelete insteadof SoftDeletes;
	}
	use Concerns\HasFilteredAttributes;

	const CREATED_BY = NULL;

	protected $attributes = [
		'is_verified' => false,
		'is_published' => false,
		'is_reported' => false,
		'is_private' => false,
	];

	protected $guarded = [
		'is_verified',
		'is_published',
		'is_reported',
		'is_private',
	];

	protected $casts = [
		'is_verified' => 'boolean',
		'is_published' => 'boolean',
		'is_reported' => 'boolean',
		'is_private' => 'boolean',
	];

	protected $dates = [
		'published_at',
		'reported_at',
	];

	protected $with = [
		'owner',
		'logo',
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

		static::addGlobalScope('order_verification', function (Builder $builder) {
			$builder->latest(static::UPDATED_AT)->latest('id');
		});
	}

	public function scopeFrontend($query) {
		$query->without([
			'verifications',
			'verifications.status',
		]);
		$query->with([
			// 'visuals',
			'logo',
			'categories',
			'tags',
			'owner',
		]);
		$query->withCount([
			// 'visuals',
		]);

		$query->isListed();

		// $query->where('id', 0); // dummy
	}

	public function scopeIsListed($query, $invert = false) {
		$query->where('is_verified', !$invert ? 1 : 0);
		$query->where('is_published', !$invert ? 1 : 0);
		$query->where('is_reported', !$invert ? 0 : 1);
		$query->where('is_private', !$invert ? 0 : 1);

		$query->whereHas('owner', function($query) {
			$query->blocked(false);
		});
	}

	public static function getFrontendItem($slug, $fail = true, $scope = true) {
		$item = null;
		$query = static::query();
		if($scope) {
			$query->frontend();
		}

		if(is_numeric($slug)) {
			$item = (clone $query)->whereKey($slug)->first();
		}
		if(!$item) {
			$fn = $fail ? 'firstOrFail' : 'first';
			$item = (clone $query)->where('slug', $slug)->$fn();
		}

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
		return $this->visual()->image();
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

	public function owner() {
		return $this->belongsTo('App\User', 'owner_id')->withDefault();
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
		return $this->hasOne('App\Models\AppVerification', 'app_id')
			->latest(AppVerification::UPDATED_AT)
			->latest('id')
		;
	}

	public function last_verifier_verification() {
		return $this->last_verification()->byVerifiers();
	}

	public function report_verification() {
		return $this->last_verification()->where('concern', AppVerification::CONCERN_REPORT);
	}

	public function latest_approved_verifications() {
		// Get latest sequential approvals
		return $this->hasMany('App\Models\AppVerification', 'app_id')->latestSequence('approved');
	}

	public function changelogs() {
		return $this->hasMany('App\Models\AppChangelog', 'app_id');
	}

	public function non_rejected_changes() {
		return $this->changelogs()->rejected(false);
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
				// ->where('id', '>', $this->version->id)
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
		return $this->hasOne('App\Models\AppChangelog', 'app_id')->latest(AppVerification::CREATED_AT)->latest('id');
	}

	public function reports() {
		return $this->hasMany('App\Models\AppReport', 'app_id');
	}

	public function verdicts() {
		return $this->hasMany('App\Models\AppVerdict', 'app_id');
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

	public function getHasNonRejectedHistoryAttribute() {
		return $this->non_rejected_changes->count() > 1;
	}

	public function getHasCommittedAttribute() {
		return $this->changelogs()->committed()->count() > 0;
	}

	public function getHasVerificationsAttribute() {
		return $this->verifications->count() > 0
			&& ($this->verifications->count() > 1 || $this->verifications->first()->status_id != 'unverified')
		;
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
		return !$this->is_verified
			// && !$this->is_published
			// && $this->changelogs()->count() == $this->floating_changes()->count()
			&& $this->verifications()->where('concern', AppVerification::CONCERN_PUBLISH_ITEM)->doesntExist()
		;
	}

	public function getIsOwnedAttribute() {
		return $this->owner_id == \Auth::id();
	}

	public function getCompleteNameAttribute() {
		// return $this->short_name ? $this->short_name .' - '. $this->name : $this->name;
		return $this->short_name ? $this->name .' ('.$this->short_name.')' : $this->name;
	}

	public function getPublicNameAttribute() {
		return $this->short_name ?: $this->name;
	}

	public function getPublicUrlAttribute() {
		return $this->get_public_url();
	}

	public function get_public_url($params = []) {
		// TODO: decide whether to use slug or ID for the public URL
		// ID	= weird number
		// Slug	= nicer, but changes with the name, so can't really be bookmarked
		if(!isset($params['slug']))
			$params['slug'] = $this->id;
		return route('apps.page', $params);
	}

	public function getIsListedAttribute() {
		return $this->is_verified
			&& $this->is_published
			&& ! $this->is_reported
			&& ! $this->is_private
		;
	}

	public function getLastChangesAtAttribute() {
		return $this->version->updated_at
			?? $this->updated_at
			?? $this->created_at
		;
		// return $this->last_changes()->committed()->value('updated_at');
	}

	public function getIsOriginalVersionAttribute() {
		return !array_key_exists('original_version_number', $this->attributes)
			|| $this->version_number == $this->original_version_number
		;
	}

	public function setToPublished($state = true) {
		$this->is_published = $state;
		if(!$state)
			$this->published_at = null;
		elseif(!$this->published_at)
			$this->published_at = now();

		return $this;
	}

	public function setToReported($state = true) {
		$this->is_reported = $state;
		$this->reported_at = $state ? now() : null;
		return $this;
	}

	public function setToPrivate($state = true) {
		$this->is_private = $state;
		return $this;
	}

	public function increasePageViews($save = true) {
		$this->page_views += 1;
		if($save) {
			// $this->dontLogNextAction();
			// return $this->save();

			// Save using a query so that timestamps don't get touched
			DB::table($this->getTable())
				->where($this->getKeyName(), $this->getKey())
				->update(['page_views' => $this->page_views])
			;
		}
	}
}
