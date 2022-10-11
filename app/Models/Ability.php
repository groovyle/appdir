<?php

namespace App\Models;

use Silber\Bouncer\Database\Ability as BaseAbility;

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

		$query->orderBy('abilities.entity_type', $ascending);
		if($with_permissions) {
			// $query->orderBy('pivot_forbidden', $ascending);
			$query->orderBy('pivot_forbidden', 'asc');
		}
		$query->orderBy('title', $ascending);
		$query->orderBy('name', $ascending);
	}
}
