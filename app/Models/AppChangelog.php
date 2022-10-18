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
	const UPDATED_AT = 'updated_at';

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
			$builder->latest()->latest('id');
		});
	}

	public function scopePending($query, $state = true) {
		$op = $state ? '=' : '!=';
		$query->where('status', $op, self::STATUS_PENDING);
	}

	public function scopeRejected($query, $state = true) {
		$op = $state ? '=' : '!=';
		$query->where('status', $op, self::STATUS_REJECTED);
	}

	public function scopeApproved($query, $state = true) {
		$op = $state ? '=' : '!=';
		// waiting to be committed
		$query->where('status', $op, self::STATUS_APPROVED);
	}

	public function scopeCommitted($query, $state = true) {
		$op = $state ? '=' : '!=';
		// waiting to be committed
		$query->where('status', $op, self::STATUS_COMMITTED);
	}

	public function scopeFloating($query, $state = true) {
		$fn = $state ? 'whereIn' : 'whereNotIn';
		// aka uncommitted, i.e pending or approved
		$query->$fn('status', [self::STATUS_PENDING, self::STATUS_APPROVED]);
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

	public function reports() {
		return $this->hasMany('App\Models\AppReport', 'version_id');
	}

	public function unresolved_reports() {
		return $this->reports()->unresolved();
	}

	public function resolved_reports() {
		return $this->reports()->resolved();
	}

	public function nextVersionNumber() {
		return $this->version + 1;
	}

	public function getDisplayDiffsAttribute() {
		return AppManager::transformDiffsForDisplay($this->diffs);
	}

	public function getIsPendingAttribute() {
		return $this->attributes['status'] == self::STATUS_PENDING;
	}

	public function getIsApprovedAttribute() {
		return $this->attributes['status'] == self::STATUS_APPROVED;
	}

	public function getIsRejectedAttribute() {
		return $this->attributes['status'] == self::STATUS_REJECTED;
	}

	public function getIsCommittedAttribute() {
		return $this->attributes['status'] == self::STATUS_COMMITTED;
	}

	public function getIsFloatingAttribute() {
		return in_array($this->attributes['status'], [self::STATUS_PENDING, self::STATUS_APPROVED]);
	}

}