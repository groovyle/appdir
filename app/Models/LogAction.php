<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAction extends Model
{
	use Concerns\Immutable;

	public $timestamps = TRUE;
	const CREATED_AT = 'at';
	const UPDATED_AT = NULL;

	protected $guarded = [
		'id',
	];

	protected $dates = [
		'at',
	];

	public function entity() {
		return $this->morphTo();
	}

	public function related() {
		return $this->morphTo();
	}

	public function actor() {
		return $this->belongsTo('App\User', 'actor_id');
	}

	public static function logModel(Model $model, $action, $actor = null, $payload = NULL, $description = NULL, Model $related = NULL) {
		$actor = $actor === null ? Auth::user() : User::find($actor);
		$data = [
			'entity_id'		=> $model->getKey(),
			'entity_type'	=> $model->getMorphClass(),
			'action'		=> $action,
			'description'	=> $description,
			'data'			=> $payload ? json_encode($payload) : NULL,
			'actor_id'		=> $actor->id,
			// 'actor_name'	=> $user->name,
			'actor_name'	=> NULL,
			'at'			=> $model->freshTimestampString(),
		];
		if($related) {
			$data = array_merge($data, [
				'related_id'	=> $related->getKey(),
				'related_type'	=> $related->getMorphClass(),
			]);
		}
		return static::insert($data);
	}

}
