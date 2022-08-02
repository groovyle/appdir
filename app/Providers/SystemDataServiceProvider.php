<?php

namespace App\Providers;

use App\SystemDataProviders\SystemDataBroker;
use Illuminate\Support\ServiceProvider;

class SystemDataServiceProvider extends ServiceProvider
{
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
		$this->app->singleton(SystemDataBroker::class, function ($app) {
			return new SystemDataBroker(config('data_provider.type', 'virtualmin'));
		});
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
