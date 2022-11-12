<?php

use Illuminate\Database\Seeder;

use App\User;
use App\Models\SystemUsers as Sysusers;
use Illuminate\Support\Facades\Hash;

class SystemUserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		config()->set('database.action_logging', false);

		$sysusers = [
			Sysusers\Automator::class,
			Sysusers\Guest::class,
		];

		foreach($sysusers as $sus_class) {
			$sus = $sus_class::instance();
			if(!$sus) {
				$sus = $sus_class::generateInstance();
				$sus->save();
			}
		}

	}
}
