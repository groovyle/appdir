<?php

namespace App\Models;

use App\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class LogAction extends Model
{

	public $timestamps = TRUE;
	const CREATED_AT = 'at';
	const UPDATED_AT = null;

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

	public static function logModel($model, $action, $actor = null, $payload = null, $description = null, $related = null) {
		$actor = $actor === null ? Auth::user() : User::find($actor);
		/*if(!$user) {
			$actor = \App\Models\SystemUsers\Guest::instance();
		}*/
		$additional_data = null;
		if($payload) {
			if(is_array($payload)) {
				$additional_data = [];
				foreach($payload as $k => $v) {
					if(is_string($v)) {
						$json_v = json_decode($v);
						if($json_v) $v = $json_v;
					}
					$additional_data[$k] = $v;
				}
			} else {
				$additional_data = $payload;
				// $additional_data = json_decode($payload);
			}
		}

		if(is_string($model) && class_exists($model)) {
			$model = new $model;
		}
		if($model instanceof Model) {
			$entity_type = $model->getMorphClass();
			$entity_id = $model->getKey() ?: null;
		} else {
			$entity_type = (string) $model;
			$entity_id = null;
		}

		$data = [
			'entity_type'	=> $entity_type,
			'entity_id'		=> $entity_id,
			'action'		=> $action,
			'description'	=> $description,
			'data'			=> $additional_data,
			'actor_id'		=> optional($actor)->id,
			// 'actor_name'	=> $user->name,
			'actor_name'	=> null,
			'at'			=> $model->freshTimestampString(),
		];
		if($related) {
			if(is_string($related) && class_exists($related)) {
				$related = new $related;
			}
			if($related instanceof Model) {
				$related_type = $related->getMorphClass();
				$related_id = $related->getKey() ?: null;
			} else {
				$related_type = (string) $related;
				$related_id = null;
			}

			$data = array_merge($data, [
				'related_type'	=> $related_type,
				'related_id'	=> $related_id,
			]);
		}

		$instance = new static($data);
		return $instance->save();
	}

}
