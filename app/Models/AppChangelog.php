<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\DataManagers\AppManager;

class AppChangelog extends Model
{
	use Concerns\HasCudActors;

	const UPDATED_BY	= null;
	const DELETED_BY	= null;

	protected $table = 'app_changelogs';

	public $timestamps = TRUE;
	const CREATED_AT = 'created_at';
	const UPDATED_AT = NULL;

	const STATUS_PENDING	= 'pending';
	const STATUS_APPROVED	= 'approved';
	const STATUS_COMMITTED	= 'committed';
	const STATUS_REJECTED	= 'rejected';

	protected $attributes = [
		'is_verified'	=> false,
		'status'		=> self::STATUS_PENDING,
	];

	protected $casts = [
		'diffs'	=> 'array',
		'is_verified' => 'boolean',
	];

	protected $guarded = [
		'id',
		'app_id',
		'based_on_id',
		'is_verified',
	];

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_order', function (Builder $builder) {
			// TODO: sort by creation order or version number?
			// $builder->orderBy('version', 'desc');
			$builder->latest();
		});
	}

	public function scopePending($query) {
		$query->where('status', self::STATUS_PENDING);
	}

	public function scopeApproved($query) {
		// waiting to be committed
		$query->where('status', self::STATUS_APPROVED);
	}

	public function scopeFloating($query) {
		// aka uncommitted, i.e pending or approved
		$query->whereIn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
	}

	public function scopeInVerifIds($query, $ids) {
		$query->whereHas('verifications', function($query) use($ids) {
			$query->whereIn('id', $ids);
		});
	}

	public function app() {
		return $this->belongsTo('App\Models\App');
	}

	public function verification() {
		return $this->hasMany('App\Models\AppVerification', 'changes_id');
	}

	public function based_on() {
		return $this->belongsTo(static::class, 'based_on_id');
	}

	public function verifications() {
		return $this->belongsToMany('App\Models\AppVerification', 'app_verification_changes', 'changes_id', 'verification_id');
	}

	public function nextVersionNumber() {
		return $this->version + 1;
	}

	public function getDisplayDiffsAttribute() {
		return AppManager::transformDiffsForDisplay($this->diffs);
	}

}