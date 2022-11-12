<?php

namespace App\Models\SystemUsers;

class Guest extends Base {

	const USER_ID = 102;

	public static function generateInstance() {
		$user = parent::generateInstance();
		$user->name = $user->email = 'system_guest_user';

		return $user;
	}

}
