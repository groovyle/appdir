<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAction extends Model
{

	public $timestamps = TRUE;
	const CREATED_AT = 'at';
	const UPDATED_AT = NULL;

	protected $guarded = [
		'id',
	];

	protected $dates = [
		'at',
	];

	protected $casts = [
		'data'	=> 'array',
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

	public function getActorNameAttribute() {
		return $this->attributes['actor_name']
			?: $this->actor->name_email ?? null
		;
	}

	public function update(array $attributes = [], array $options = []) {
		return false;
	}

	public function delete() {
		return false;
	}

	public static function logModel(Model $model, $action, $actor = null, $payload = NULL, $description = NULL, Model $related = NULL) {
		$actor = $actor === null ? Auth::user() : User::find($actor);
		/*if(!$user) {
			$actor = \App\Models\SystemUsers\Guest::instance();
		}*/
		$additional_data = null;
		if($payload) {
			$additional_data = is_array($payload) ? array_map('json_decode', $payload) : json_decode($payload);
		}
		$data = [
			'entity_id'		=> $model->getKey(),
			'entity_type'	=> $model->getMorphClass(),
			'action'		=> $action,
			'description'	=> $description,
			'data'			=> $additional_data,
			'actor_id'		=> optional($actor)->id,
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

		$instance = new static($data);
		return $instance->save();
	}

}
