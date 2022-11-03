<?php

namespace App\Models;

use Bouncer;

use Silber\Bouncer\Database\Ability as BaseAbility;
use Silber\Bouncer\Database\Titles\AbilityTitle;
use Silber\Bouncer\Database\Models as BouncerModels;

class Ability extends BaseAbility
{
	/**
	 * Just a proxy so naming gets easier, I guess.
	 * Also just a placeholder in case there's a need to extend the default model.
	 *
	 */

	public function scopeDefaultOrder($query, $asc = true, $with_permissions = false) {
		$ascending = $asc ? 'asc' : 'desc';
		$descending = $asc ? 'desc' : 'asc';

		$query->orderBy('entity_type', $ascending);
		if($with_permissions) {
			// $query->orderBy('pivot_forbidden', $ascending);
			$query->orderBy('pivot_forbidden', 'asc');
		}
		$query->orderBy('title', $ascending);
		$query->orderBy('name', $ascending);
	}


	public function syncUsers($users, $refresh_cache = true) {
		$user_ids = [];
		if($users instanceof EloCollection) {
			// $user_ids = $users->modelKeys();
		} elseif($users instanceof Collection) {
			$users = new EloCollection($users->all());
		} else {
			$user_ids = (array) $users;
			$users = BouncerModels::user()->findMany($user_ids);
		}

		$current_users = $this->users;

		$users_to_remove = $current_users->diff($users);
		$users_to_add = $users->diff($current_users);

		foreach($users_to_remove as $u) {
			Bouncer::disallow($u)->to($this);
		}
		foreach($users_to_add as $u) {
			Bouncer::allow($u)->to($this);
		}

		if($refresh_cache) {
			// Refresh users authorization cache
			foreach($users_to_remove->concat($users_to_add) as $u) {
				Bouncer::refreshFor($u);
			}
		}
	}


	public function getAliasedEntityTypeAttribute() {
		$entity_type = $this->attributes['entity_type'] ?? null;
		if(!$entity_type && $entity_type == '*')
			return $entity_type;

		return get_morph_model($entity_type, false) != null;
	}

	public function getDisplayEntityTypeAttribute() {
		$entity_type = $this->attributes['entity_type'] ?? null;
		if(!$entity_type && $entity_type == '*')
			return $entity_type;

		return get_morph_model($entity_type, true);
	}

}
