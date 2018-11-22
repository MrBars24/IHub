<?php

namespace App\Http\ViewComposers;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
	public function boot()
	{
		view()->composer('master._layout.master', function($view) {
			// auth_user, actor
			$user = $actor = auth()->user();
			if(!is_null($user)) {
				$user = $user->getActor();
			}

			$view->with('auth_user', $user);
			$view->with('actor', $actor);
		});

		view()->composer('master._layout.header', function($view) {
			// account drop down
			$view->with('links', [
				['href' => route('auth::logout'),  'text' => 'Settings'],
				//['href' => route('auth::logout'), 'text' => 'Change Password'],
				['href' => route('auth::logout'),  'text' => 'Logout'],
			]);
		});

		view()->composer('components.sidebar', function($view) {
			// sidebar
			$view->with('sidebar', [
				'hubs' => ['href' => route('master::hub.index'), 'icon' => 'fa-building', 'label' => 'Hubs'],
				'users' => ['href' => route('master::user.index'), 'icon' => 'fa-users', 'label' => 'Users'],
				'staffs' => ['href' => route('master::staff.index'), 'icon' => 'fa-star', 'label' => 'Staffs'],
				'packages' => ['href' => route('master::package.index'), 'icon' => 'fa-cube', 'label' => 'Packages'],
				'logs' => ['href' => route('master::log.index',["logtype" => "postdispatch"]), 'icon' => 'fa-list', 'label' => 'Logs'],
			]);
		});

		view()->composer('master.hub.*', function($view) {
			$view->with('active_sidebar', 'hubs');
		});

		view()->composer('master.user.*', function($view) {
			$view->with('active_sidebar', 'users');
		});

		view()->composer('master.staff.*', function($view) {
			$view->with('active_sidebar', 'staffs');
		});

		view()->composer('master.log.*', function($view) {
			$view->with('active_sidebar', 'logs');
		});
	}
}