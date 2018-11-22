<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Carbon\Carbon;

class AuthServiceProvider extends ServiceProvider
{
	/**
	 * The policy mappings for the application.
	 *
	 * @var array
	 */
	protected $policies = [
		'App\Model' => 'App\Policies\ModelPolicy',
	];

	/**
	 * Register any authentication / authorization services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerPolicies();

		// register the passport routes
		Passport::routes();

		Passport::enableImplicitGrant();

		Passport::tokensExpireIn(carbon()->addDays(config('auth.expire.access_token')));

		Passport::refreshTokensExpireIn(carbon()->addDays(config('auth.expire.access_token')));
	} 

}
