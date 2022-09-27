<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserBlock extends Model
{
	protected $table = 'user_blocks';

	use SoftDeletes,
		Concerns\HasCudActors;

	protected $casts = [
		'details' => 'array',
	];

	public function user() {
		return $this->belongsTo('App\User', 'user_id');
	}

	public function getIsLiftedAttribute() {
		return $this->trashed();
	}

}
