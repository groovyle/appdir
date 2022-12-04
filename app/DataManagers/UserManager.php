<?php

namespace App\DataManagers;

use App\User;
use App\Models\Role;

use Auth;
use Bouncer;

use App\Notifications\Messages\MailMessage;

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

	public static function userHighestRole(User $user = null, $as_model = false) {
		if(!$user)
			$user = Auth::user();

		$sorted = static::getSortedRoles($user->roles);
		$role = null;
		if(isset($sorted[0]))
			return $as_model ? $sorted[0] : $sorted[0]->name;

		return null;
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

	public static function scopeListQuery($query, &$view_mode, $user = null) {
		if(!$user)
			$user = Auth::user();

		// Scope filters
		$view_mode = static::userViewMode($user);
		$query->where(function($query) use($user, &$view_mode) {
			if($view_mode == 'all') {
				// No scope filter, enable all
				$query->whereRaw('1');
			} elseif($view_mode == 'prodi') {
				// Only ones in the same prodi
				$query->whereHas('prodi', function($query) use($user) {
					$query->where('id', $user->prodi_id);
					$query->whereNotNull('id');
				});
			} else {
				// None
			}
		});

		if($view_mode == 'none') {
			$query->whereRaw('0 = 1');
		}
	}


	// Verification email
	public static function verifyEmailMail($notifiable, $verificationUrl) {
		// return (new VerifyEmailMail($notifiable, $verificationUrl))->to($notifiable->email);
		return (new MailMessage)
			->fromNoReply()
			->subject(__('mails.verify_account.subject'))
			->greeting(__('mails.verify_account.greeting', ['user' => $notifiable->name]))
			->line(__('mails.verify_account.intro'))
			->action(__('mails.verify_account.action'), $verificationUrl)
			->line(__('mails.verify_account.outro'))
			->salutation(__('mails.salutation'))
		;
	}


}
