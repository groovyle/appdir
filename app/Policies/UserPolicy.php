<?php

namespace App\Policies;

use App\User;
use Bouncer;
use App\DataManagers\UserManager;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view any models.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function viewAny(User $user)
	{
		//
	}

	protected function standardProdiCheck(User $user, User $model = null) {
		// Bypasses prodi rule
		if($user->can('bypass-prodi', User::class)) {
			return;
		}

		// Must be in the same prodi
		if(!$user->prodi_id || $user->prodi_id != optional($model)->prodi_id) {
			return false;
		}
	}

	protected function standardModelCheck(User $user, User $model) {
		if($model->exists && $model->is_system)
			return false;

		return $this->standardProdiCheck($user, $model);
	}

	/**
	 * Determine whether the user can view the model.
	 *
	 * @param  \App\User  $user
	 * @param  \App\User  $model
	 * @return mixed
	 */
	public function view(User $user, User $model)
	{
		return $this->standardProdiCheck($user, $model);
	}

	/**
	 * Determine whether the user can create models.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function create(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can update the model.
	 *
	 * @param  \App\User  $user
	 * @param  \App\User  $model
	 * @return mixed
	 */
	public function update(User $user, User $model)
	{
		//
		$check = $this->standardModelCheck($user, $model);
		if($check === false) return false;

		// Can update self
		if($model->is_me) return true;

		// Cannot edit users on the same level or above - this means can only
		// edit for users below in hierarchy
		$role_compare = UserManager::userRoleCompare($user, $model);
		if($role_compare < 1) return false;
	}

	/**
	 * Determine whether the user can delete the model.
	 *
	 * @param  \App\User  $user
	 * @param  \App\User  $model
	 * @return mixed
	 */
	public function delete(User $user, User $model)
	{
		//
		return $this->manipulateAccount($user, $model);
	}

	/**
	 * Determine whether the user can restore the model.
	 *
	 * @param  \App\User  $user
	 * @param  \App\User  $model
	 * @return mixed
	 */
	public function restore(User $user, User $model)
	{
		//
		$check = $this->standardModelCheck($user, $model);
		if($check === false) return false;

		// Cannot edit users on the same level or above - this means can only
		// edit for users below in hierarchy
		$role_compare = UserManager::userRoleCompare($user, $model);
		if($role_compare < 1) return false;
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 *
	 * @param  \App\User  $user
	 * @param  \App\User  $model
	 * @return mixed
	 */
	public function forceDelete(User $user, User $model)
	{
		// No permanent deletion pls
		return false;
	}

	public function manipulateAccount(User $user, User $model, $allow_equal = false)
	{
		// Account manipulation is different to just editing profile information,
		// in that the manipulation here could affect a target user's access to
		// their account.

		$check = $this->standardModelCheck($user, $model);
		if(!is_null($check)) return $check;

		// Cannot manipulate own account - for that, go to profile page instead
		if($model->is_me) return false;


		// Cannot manipulate users on the same level or above - this means can only
		// manipulate for users below in hierarchy
		$role_compare = UserManager::userRoleCompare($user, $model);
		if($role_compare < ($allow_equal ? 0 : 1)) return false;
	}

	public function resetPassword(User $user, User $model)
	{
		return $this->manipulateAccount($user, $model);
	}

	public function block(User $user, User $model, $check_blocked = true)
	{
		$manipulate = $this->manipulateAccount($user, $model);
		if(!is_null($manipulate)) return $manipulate;

		// Is it already blocked?
		if($check_blocked && $model->is_blocked) {
			// return false;
			return $this->deny(__('admin/users.messages.user_is_already_blocked'));
		}
	}

	public function unblock(User $user, User $model, $check_blocked = true)
	{
		$manipulate = $this->manipulateAccount($user, $model, true);
		if(!is_null($manipulate)) return $manipulate;

		// Is it not blocked?
		if($check_blocked && !$model->is_blocked) {
			// return false;
			return $this->deny(__('admin/users.messages.user_is_not_blocked'));
		}
	}


	public function viewPublic(User $user = null, User $model) {
		if(!$model)
			return false;

		$is_admin = $user && $user->isA('superadmin', 'admin');
		if($model->is_blocked) {
			if(!$is_admin) {
				return false;
			}
		}

		if($user && $user->id == $model->id) {
			// Self
			return true;
		}

		if(!$user) {
			// Bypass Bouncer because if the user is a guest, Bouncer checks
			// will always return false
			return true;
		}
	}
}
