<?php

namespace App\Models\Policies;

use App\Models\App;
use App\Models\AppVerification;
use App\Models\AppChangelog;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppVerificationPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view any app verifications.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function viewAny(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can view the app verification.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function view(User $user, AppVerification $verif)
	{
		//
	}

	/**
	 * Determine whether the user can create app verifications.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function create(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can update the app verification.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function update(User $user, AppVerification $verif)
	{
		// Must exist
		if(!$verif->exists)
			return false;

		// NOTE: can only be edited by the verifier themself, or...?
		if($user->id != $verif->verifier_id) {
			return false;
		}

		// TODO: maybe add a time restriction (e.g only 24 hours after last update?)
		if($verif->id == $verif->app->last_verification->id
			&& $verif->status->by == 'verifier'
			&& $verif->concern == AppVerification::CONCERN_VERIFICATION
		) {
			// Is the last verification
			if($verif->status_id == 'approved') {
				// Can only edit approved ones if the changes were not committed yet
				$can = $verif->changelogs->every(function($item, $key) {
					return $item->status == AppChangelog::STATUS_APPROVED;
				});
				if(!$can)
					return false;
				else
					return;
			} else {
				return;
			}
		}

		return false;
	}

	/**
	 * Determine whether the user can do reviews.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function review(User $user, AppVerification $verif = null, App $app = null)
	{
		// NOTE: $app is there for maybe when we need additional checks, but
		// we shouldn't need it

		// Can review if they can create OR update
		return $user->can('create', AppVerification::class)
			|| $user->can('update', $verif ?? new AppVerification)
		;
	}

	/**
	 * Determine whether the user can delete the app verification.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function delete(User $user, AppVerification $verif)
	{
		//
	}

	/**
	 * Determine whether the user can restore the app verification.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function restore(User $user, AppVerification $verif)
	{
		//
	}

	/**
	 * Determine whether the user can permanently delete the app verification.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\AppVerification  $verif
	 * @return mixed
	 */
	public function forceDelete(User $user, AppVerification $verif)
	{
		//
	}
}
