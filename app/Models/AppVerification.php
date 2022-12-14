<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AppVerification extends Model
{
	//
	use Concerns\HasCudActors;

	const CREATED_BY	= 'verifier_id';
	const UPDATED_BY	= null;
	const DELETED_BY	= null;

	const CONCERN_NEW_ITEM		= 'new'; // for new items
	const CONCERN_EDIT_ITEM		= 'edit'; // for pending changes
	const CONCERN_COMMIT		= 'commit'; // for applying approved changes
	const CONCERN_PUBLISH_ITEM	= 'publish'; // for publishing item, usually also applying changes
	const CONCERN_VERSION_SWITCH	= 'switchver'; // for switching to a version
	const CONCERN_REPORT		= 'report'; // for verifications concerning reports
	const CONCERN_DELETE_ITEM	= 'delete'; // for item deletion
	const CONCERN_RESTORE_ITEM	= 'restore'; // for item restoration
	const CONCERN_VERIFICATION	= 'verification'; // else

	protected $attributes = [
		'concern'		=> self::CONCERN_VERIFICATION,
	];

	protected $app_column = 'app_id';
	protected $casts = [
		'details' => 'array'
	];

	public $details_order = [
		'name',
		'short_name',
		'url',
		'logo',
		'description',
		'categories',
		'tags',
		'visuals',
	];

	public $with = [
		'verifier',
		'status',
		'verdict',
	];

	public function scopeLatestSequence($query, $status = 'approved') {
		$t = $this->getTable();
		return
		$query->select($t.'.*')
			->leftJoin($t.' as v2', function($query) use($status, $t) {
				$query->on('v2.app_id', '=', $t.'.app_id')
					->where('v2.status_id', '<>', $status)
					->on('v2.created_at', '>', $t.'.created_at')
					->on('v2.id', '<>', $t.'.id')
				;
			})
			->where($t.'.status_id', $status)
			->whereNull('v2.id')
		;
	}

	public function scopeByVerifiers($query) {
		$query->whereHas('status', function($query) {
			$query->where('by', VerifierVerificationStatus::ACTOR);
		});
	}

	public function scopeByEditors($query) {
		$query->whereHas('status', function($query) {
			$query->where('by', EditorVerificationStatus::ACTOR);
		});
	}

	public function getAppColumn() {
		return $this->app_column;
	}

	public function getQualifiedAppColumn() {
		return $this->qualifyColumn($this->getAppColumn());
	}

	public function app() {
		return $this->belongsTo('App\Models\App', $this->getAppColumn());
	}

	public function verifier() {
		return $this->belongsTo('App\User', 'verifier_id');
	}

	public function status() {
		return $this->belongsTo('App\Models\VerificationStatus', 'status_id');
	}

	public function base_changelog() {
		return $this->belongsTo('App\Models\AppChangelog', 'base_changes_id');
	}

	public function changelogs() {
		return $this->belongsToMany('App\Models\AppChangelog', 'app_verification_changes', 'verification_id', 'changes_id');
	}

	public function verdict() {
		return $this->hasOne('App\Models\AppVerdict', 'verification_id')->latest();
	}

	public function getChangelogRangeAttribute() {
		$changes = $this->changelogs->reverse()->values();
		return new AppChangelogCollection($changes);
	}

	public function getOrderedDetailsAttribute() {
		return collect($this->details)->sortBy(function($item, $key) {
			return ($pos = array_search($key, $this->details_order)) !== false
				? $pos
				: 999
			;
		})->all();
	}

	public function getIsReportedGuiltyAttribute() {
		return !!optional($this->verdict)->is_guilty;
	}

}
