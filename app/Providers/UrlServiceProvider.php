<?php

namespace App\Providers;

// App
use App\Modules\Urls\UrlManager;

// Laravel
use Illuminate\Support\ServiceProvider;

class UrlServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(UrlManager::class, function($app) {
			return new UrlManager;
		});
	}
}
