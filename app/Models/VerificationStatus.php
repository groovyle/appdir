<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class VerificationStatus extends Model
{
	use SoftDeletes;
	use Concerns\LoggedActions;
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

	public function getNameAttribute() {
		$id = $this->getKey();
		$name = $this->attributes['name'] ?? null;
		$name_key = 'common.app_verification_statuses.'.$id;

		if(\Lang::has($name_key))
			return \Lang::get($name_key);
		else
			return $name;
	}

	public function getRawNameAttribute() {
		return $this->attributes['name'] ?? null;
	}

}
