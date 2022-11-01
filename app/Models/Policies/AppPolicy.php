<?php

namespace App\Models\Policies;

use App\Models\App;
use App\Models\AppChangelog;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Gate;

class AppPolicy
{
	use HandlesAuthorization;

	/**
	 * Determine whether the user can view any apps.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function viewAll(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can view a list of owned apps.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function viewAny(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can view an app details.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function view(User $user, App $app)
	{
		$owned = $user->id == $app->owner_id;

		// Views all
		if($user->can('view-all', App::class)) {
			return;
		}

		// Owned
		if($owned) {
			return;
		}

		// Only ones in the same prodi
		if($user->can('view-any-in-prodi', App::class)) {
			$user_prodi_id = $user->prodi->id ?? null;
			$app_prodi_id = $app->owner->prodi->id ?? null;
			if($user_prodi_id == null || $user_prodi_id != $app_prodi_id)
				return false;
		}

	}

	/**
	 * Determine whether the user can view non-owned apps' past versions.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function viewVersion(User $user, App $app)
	{
		//
	}

	/**
	 * Determine whether the user can view an app's public page/information
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function viewPublic(User $user = null, App $app = null, $inspect = false)
	{
		$result = [
			'status'	=> false,
			'view_mode'	=> 'none',
		];
		$return = function() use($inspect, &$result) {
			return $inspect
				? ($result['status'] ? $this->allow($result) : $this->deny(null, 404))
				: $result['status']
			;
		};

		if(!$app)
			return $return();

		// User could be null, i.e public, anonymous user
		if($user) {
			$is_owner = $user->id == $app->owner_id;
			$is_admin = $user->isA('superadmin', 'admin');

			if($is_admin) {
				if(!$app->is_original_version) {
					if($user->cannot('view-version', $app)) {
						$result['status'] = false;
						return $return();
					}
				}

				$result['status'] = true;
				$result['view_mode'] = 'admin';
				return $return();
			}

			if($user->cannot('view-public', $app->owner)) {
				$result['status'] = false;
				return $return();
			}

			// Owner
			if($is_owner) {
				$result['status'] = true;
				$result['view_mode'] = 'owner';
				return $return();
			}
		}

		// Check app owner
		if(Gate::forUser($user)->denies('view-public', $app->owner)) {
			$result['status'] = false;
			return $return();
		}

		$result['status'] = $app->is_verified
			&& $app->is_published
			&& ! $app->is_reported
			&& ! $app->is_private
		;
		return $return();
	}

	/**
	 * Determine whether the user can create apps.
	 *
	 * @param  \App\User  $user
	 * @return mixed
	 */
	public function create(User $user)
	{
		//
	}

	/**
	 * Determine whether the user can update the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function update(User $user, App $app)
	{
		//
		if($user->can('update-all', App::class)) return true;
		if(!$app->is_owned) return false;
	}

	/**
	 * Determine whether the user can delete the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function delete(User $user, App $app)
	{
		//
		if($user->can('delete-all', App::class)) return true;
		if(!$app->is_owned) return false;
	}

	/**
	 * Determine whether the user can restore the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function restore(User $user, App $app)
	{
		//
		if($user->can('delete-all', App::class)) return true;
		if(!$app->is_owned) return false;
	}

	/**
	 * Determine whether the user can permanently delete the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\Models\App  $app
	 * @return mixed
	 */
	public function forceDelete(User $user, App $app)
	{
		// No permanent deletion pls
		return false;
	}

	public function switchToVersion(User $user, App $app, $version) {
		$update = $user->can('update', $app);
		if(!$update)
			return false;

		if(!$app->version)
			return false;

		if(!($version instanceof AppChangelog))
			$version = $app->changelogs()->where('version', $version)->first();

		if(!$version) // nothing found
			return false;
		elseif($app->id != $version->app_id) // who???
			return false;
		elseif($app->version_number == $version->version) // cant switch to self
			return false;
		elseif($version->updated_at > $app->version->updated_at) // TODO:? target is from the future
			return false;
		elseif(!$version->is_committed) // not an applied version
			return false;
	}
}
