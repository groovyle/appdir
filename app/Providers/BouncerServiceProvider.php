<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Bouncer;
use Silber\Bouncer\Bouncer as BaseBouncer;
use Illuminate\Container\Container;

use App\Models\Ability;
use App\Models\Role;

class BouncerServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Dependency injection
		Container::getInstance()->singleton(BaseBouncer::class, function () {
			return BaseBouncer::create();
		});

		// Use our own models
		Bouncer::useAbilityModel(Ability::class);
		Bouncer::useRoleModel(Role::class);

		// Cross-request caching
		Bouncer::cache();


		// Define model ownerships here
		Bouncer::ownedVia(\App\Models\App::class, 'owner_id');
		Bouncer::ownedVia(\App\Models\AppVerification::class, 'verifier_id');
		Bouncer::ownedVia(\App\Models\AppReport::class, 'user_id');
		Bouncer::ownedVia(\App\Models\AppChangelog::class, 'created_by');
		Bouncer::ownedVia(\App\Models\AppVisualMedia::class, 'created_by');
		Bouncer::ownedVia(\App\Models\AppTag::class, 'created_by');
	}
}
