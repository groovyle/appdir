<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class VerificationStatus extends Model
{
	use SoftDeletes;
	//
	protected $table = 'ref_verification_status';
	protected $primaryKey = 'id';
	protected $keyType = 'string';
	public $incrementing = false;

	const STATUS_UNVERIFIED = 'unverified';

	public function app_verifications() {
		return $this->hasMany('App\Models\AppVerification', 'status_id');
	}

	public static function getDefault()
	{
		return static::find(static::STATUS_UNVERIFIED);
	}
}
