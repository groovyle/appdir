<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;

class RelationMapServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		Relation::morphMap([
			// 'user' => App\User::class,
			// 'm_role' => App\Models\Role::class,
			// 'm_ability' => App\Models\Ability::class,
		]);
	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}
}
