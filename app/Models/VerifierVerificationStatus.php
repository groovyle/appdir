<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\VerificationStatus;

class VerifierVerificationStatus extends VerificationStatus
{

	const ACTOR = 'verifier';

	public static function boot() {
		parent::boot();

		static::addGlobalScope('_actor', function (Builder $builder) {
			$builder->where('by', self::ACTOR);
		});
	}

}
