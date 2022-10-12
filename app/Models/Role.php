<?php

namespace App\Models;

use Bouncer;

use Silber\Bouncer\Database\Role as BaseRole;
use Silber\Bouncer\Database\Titles\RoleTitle;
use Silber\Bouncer\Database\Models as BouncerModels;

class Role extends BaseRole
{
	/**
	 * Just a proxy so naming gets easier, I guess.
	 * Also just a placeholder in case there's a need to extend the default model.
	 *
	 */

	public static function boot() {
		parent::boot();

		/**
		 * From Silber\Bouncer\Database\Concerns\IsRole
		 * Title auto-generation was only done during creation. Make this also
		 * available when editing.
		 **/
		static::updating(function ($role) {
			if (is_null($role->title)) {
				$role->title = RoleTitle::from($role)->toString();
			}
		});
	}

	public function scopeDefaultOrder($query, $asc = true) {
		$ascending = $asc ? 'asc' : 'desc';
		$descending = $asc ? 'desc' : 'asc';

		$query->orderBy('title', $ascending);
		$query->orderBy('name', $ascending);
	}

	/*public function all_abilities() {
		return parent::abilities();
	}

	public function abilities() {
		return $this->all_abilities()->wherePivot('forbidden', 0);
	}*/

	public function allowed_abilities() {
		return $this->abilities()->wherePivot('forbidden', 0);
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

		Bouncer::retract($this)->from($users_to_remove->modelKeys());
		Bouncer::assign($this)->to($users_to_add->modelKeys());

		if($refresh_cache) {
			// Refresh users authorization cache
			foreach($users_to_remove->concat($users_to_add) as $u) {
				Bouncer::refreshFor($u);
			}
		}
	}

}
