<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVerification extends Model
{
	//
	protected $app_column = 'app_id';
	protected $casts = [
		'details' => 'array'
	];

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

	public function changes() {
		return $this->belongsToMany('App\Models\AppChangelog', 'app_verification_changes', 'verification_id', 'changes_id');
	}
}
