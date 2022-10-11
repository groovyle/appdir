<?php

namespace App\Policies;

use App\App\Models\App;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function view(User $user, App $app)
	{
		//
	}

	/**
	 * Determine whether the user can view non-owned apps' past versions.
	 *
	 * @param  \App\User  $user
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function viewVersion(User $user, App $app)
	{
		//
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
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function update(User $user, App $app)
	{
		//
	}

	/**
	 * Determine whether the user can delete the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function delete(User $user, App $app)
	{
		//
	}

	/**
	 * Determine whether the user can restore the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function restore(User $user, App $app)
	{
		//
	}

	/**
	 * Determine whether the user can permanently delete the app.
	 *
	 * @param  \App\User  $user
	 * @param  \App\App\Models\App  $app
	 * @return mixed
	 */
	public function forceDelete(User $user, App $app)
	{
		//
	}
}
