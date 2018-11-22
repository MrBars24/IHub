<?php

namespace App\Providers;

// Laravel
use Laravel\Passport\PassportServiceProvider;

class PassportBridgeServiceProvider extends PassportServiceProvider
{
	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(__DIR__.'/../resources/views', 'passport');

		$this->deleteCookieOnLogout();

		//if ($this->app->runningInConsole()) {
			$this->registerMigrations();

			$this->publishes([
				__DIR__.'/../resources/views' => base_path('resources/views/vendor/passport'),
			], 'passport-views');

			$this->publishes([
				__DIR__.'/../resources/assets/js/components' => base_path('resources/assets/js/components/passport'),
			], 'passport-components');

			$this->commands([
				\Laravel\Passport\Console\InstallCommand::class,
				\Laravel\Passport\Console\ClientCommand::class,
				\Laravel\Passport\Console\KeysCommand::class,
			]);
		//}
	}
}
