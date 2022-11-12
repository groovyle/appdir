<?php

namespace App\Models\SystemUsers;

class Automator extends Base {

	const USER_ID = 101;

	public static function generateInstance() {
		$user = parent::generateInstance();
		$user->name = $user->email = 'system_automator_user';

		return $user;
	}

}
