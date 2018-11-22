<?php

namespace App\Providers;

// Laravel
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

// Models
use App\Hub;

use Carbon\Carbon;
use Adaojunior\Passport\SocialUserResolverInterface;
use App\Components\SocialUserResolver;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// Enable query loader on any non-production environment.
		if(app()->environment() != 'production') {
			DB::enableQueryLog();
		}

		// register service providers in debug mode only
		if(app()->environment() == 'local') {
			// i don't know why but PHPDebugbar is not defined 
			// on my machine so i have to disable it.
			// "20 Uncaught ReferenceError: PhpDebugBar is not defined"
			// causing the jQuery is not defined. 

			// app()->register(\Barryvdh\Debugbar\ServiceProvider::class);
		}
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		 $this->app->singleton(SocialUserResolverInterface::class, SocialUserResolver::class);
	}
}
