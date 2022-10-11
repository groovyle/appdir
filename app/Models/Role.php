<?php

namespace App\Models;

use Silber\Bouncer\Database\Role as BaseRole;
use Silber\Bouncer\Database\Titles\RoleTitle;

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
}
