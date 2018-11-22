<?php

namespace App\Providers;

// App
use App\Modules\Files\FileManager;

// Laravel
use Illuminate\Support\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton(FileManager::class, function($app) {
			return new FileManager;
		});
	}
}
