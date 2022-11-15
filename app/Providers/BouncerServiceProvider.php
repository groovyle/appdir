<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Bouncer;
use Silber\Bouncer\Bouncer as BaseBouncer;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Relations\Relation;

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
		// Allow for policy checks to run first
		Bouncer::runAfterPolicies();

		// Dependency injection
		Container::getInstance()->singleton(BaseBouncer::class, function () {
			return BaseBouncer::create();
		});

		/**
		 * IMPORTANT NOTE: when making rules, make sure to check
		 * Relation::$morphMap because that's what's stored in the database.
		 * So when making and checking rules, make sure to use Bouncer's available
		 * methods instead of making our own model from scratch (Bouncer seems
		 * to have special codes in place to make sure that morph translations
		 * and whatnot works correctly).
		 *
		 * SEE: App\Providers\RelationMapServiceProvider
		 *
		 * In particular, the Role and Ability models are silently morph-mapped
		 * by Bouncer, but without an alias, so they default to their table names,
		 * `roles` and `abilities`, respectively.
		 * */

		// Use our own models
		Bouncer::useAbilityModel(Ability::class);
		Bouncer::useRoleModel(Role::class);

		// Cross-request caching
		Bouncer::cache();
		// Bouncer::dontCache();
		// Bouncer::refresh();


		// Define model ownerships here
		Bouncer::ownedVia(\App\Models\App::class, 'owner_id');
		Bouncer::ownedVia(\App\Models\AppVerification::class, 'verifier_id');
		Bouncer::ownedVia(\App\Models\AppReport::class, 'user_id');
		Bouncer::ownedVia(\App\Models\AppChangelog::class, 'created_by');
		Bouncer::ownedVia(\App\Models\AppVisualMedia::class, 'created_by');
		Bouncer::ownedVia(\App\Models\AppTag::class, 'created_by');
	}
}
