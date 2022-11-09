<?php

use Illuminate\Database\Seeder;

class BouncerSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		config()->set('database.action_logging', false);

		Bouncer::role()->newQuery()->withoutGlobalScopes()->delete();
		Bouncer::ability()->newQuery()->withoutGlobalScopes()->delete();


		$this->abilities();


		// ===================== ROLES ======================== //
		$this->superadmin();

		$this->admin();

		$this->mahasiswa();


		// Everyone
		// Bouncer::allow(null)->to(['view'], \App\Models\Example::class);
	}

	public function abilities() {

		// View list of all apps, even the non-owned ones
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'view-any-in-prodi']);
		// View non owned apps' past versions
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'view-version']);
		// View all apps, even the ones not in the same prodi
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'view-all']);
		// Update non owned apps
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'update-all']);
		// Delete non owned apps
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'delete-all']);
		// App: set to private
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'set-private']);
		// App: set to published
		Bouncer::ability()::createForModel('App\Models\App', ['name' => 'set-published']);

		// Interact with all users, even the ones not in the same prodi
		Bouncer::ability()::createForModel('App\User', ['name' => 'bypass-prodi']);

	}

	public function superadmin() {
		// ----- superadmin - everything
		Bouncer::allow('superadmin')->everything();
	}

	public function admin() {
		// ----- admin

		// System menus
		// Admins can still manage users, but only within their own prodi.
		// These things are later defined at the Gate codes.
		// Bouncer::forbid('admin')->toManage(\App\User::class);
		Bouncer::allow('admin')->toManage(\App\User::class);
		Bouncer::forbid('admin')->to('bypass-prodi', \App\User::class);


		// Base data management
		Bouncer::allow('admin')->toManage(\App\Models\AppCategory::class);
		Bouncer::allow('admin')->toManage(\App\Models\AppTag::class);


		// App management
		Bouncer::allow('admin')->to([
			'view-any',
			'view',
			'view-any-in-prodi',
			'view-version',
			'set-published',
		], \App\Models\App::class);

		// App verifications
		Bouncer::allow('admin')->toManage(\App\Models\AppVerification::class);

		// App reports and verdicts management should always come in pairs
		Bouncer::allow('admin')->toManage(\App\Models\AppReport::class);
		Bouncer::allow('admin')->toManage(\App\Models\AppVerdict::class);

		// Stats
		Bouncer::allow('admin')->to('view', \App\Models\StatsAppActivities::class);

		// Admins shouldn't be able to force delete SoftDeletes
		Bouncer::forbid('admin')->to('force-delete', '*');
	}

	public function mahasiswa() {
		// ----- mahasiswa
		Bouncer::allow('mahasiswa')->toOwn(\App\Models\App::class);
		Bouncer::allow('mahasiswa')->to(['view-any', 'create'], \App\Models\App::class);
		Bouncer::forbid('mahasiswa')->to('view-all', \App\Models\App::class);
		Bouncer::forbid('mahasiswa')->to('view-version', \App\Models\App::class);
		Bouncer::forbid('mahasiswa')->to('set-published', \App\Models\App::class);

		Bouncer::allow('mahasiswa')->to(['view'], \App\Models\AppVerification::class);
	}

}
