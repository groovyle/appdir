<?php

namespace App\DataManagers;

use App\User;
use App\Models\Role;

use Auth;
use Bouncer;

class UserManager {

	public static $role_order = [
		'superadmin',
		'admin',
		'mahasiswa',
	];

	public static function userViewMode(User $user = null) {
		if(!$user)
			$user = Auth::user();

		$view_mode = '';
		if($user->can('bypass-prodi', User::class)) {
			// No scope filter, enable all
			$view_mode = 'all';
		} elseif($user->prodi_id) {
			// Only ones in the same prodi
			$view_mode = 'prodi';
		} else {
			// None
			$view_mode = 'none';
		}

		return $view_mode;
	}

	public static function userRoleLevel(User $user = null) {
		if(!$user)
			$user = Auth::user();

		$roles = static::$role_order;

		$top = count($roles);
		$level = 0;
		$bu = Bouncer::is($user);
		foreach($roles as $i => $role) {
			if($bu->a($role)) {
				$level = $top - $i;
				break;
			}
		}

		return $level;
	}

	public static function userIsHighestLevel(User $user = null) {
		if(!$user)
			$user = Auth::user();

		return Bouncer::is($user)->a(static::$role_order[0]);
	}

	public static function roleIsHighestLevel($role = null) {
		if(is_numeric($role))
			$role = Role::find($role);

		$role_name = $role instanceof Role ? $role->name : (string) $role;
		$role_name = strtolower($role_name);

		return $role_name == static::$role_order[0];
	}

	public static function roleLevel($role) {
		if(is_numeric($role))
			$role = Role::find($role);

		$role_name = $role instanceof Role ? $role->name : (string) $role;
		$role_name = strtolower($role_name);

		$i = array_search($role_name, static::$role_order);
		$level = 0;
		if($i !== false) {
			$level = count(static::$role_order) - $i;
		}

		return $level;
	}

	public static function getSortedRoles($roles = null) {
		if($roles === null)
			$roles = Role::defaultOrder()->get();

		return $roles->sortByDesc(function($r) {
			return static::roleLevel($r);
		})->values();
	}

	public static function getLowerRoles($allow_equal = true, $roles = null, $user = null) {
		if($roles === null)
			$roles = static::getSortedRoles($roles);
		if(!$user)
			$user = Auth::user();

		$level = static::userRoleLevel($user);

		return $roles->filter(function($r) use($level, $allow_equal) {
			$rlevel = static::roleLevel($r);
			return $allow_equal
				? ($rlevel <= $level)
				: ($rlevel < $level)
			;
		});
	}

	public static function userRoleCompare(User $a, User $b) {
		return static::userRoleLevel($a) <=> static::userRoleLevel($b);
	}

}
