<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		// scheduled
		\App\Console\Commands\SendAlerts::class,
		\App\Console\Commands\SendErrors::class,
		\App\Console\Commands\NotifyGigs::class,
		\App\Console\Commands\DispatchPosts::class,
		\App\Console\Commands\PublishPosts::class,
		\App\Console\Commands\CacheSocialPages::class,
		\App\Console\Commands\CacheSocialFields::class,
		\App\Console\Commands\CacheSocialConnections::class,
		\App\Console\Commands\LoadGigFeeds::class,
		\App\Console\Commands\CleanupFiles::class,
		\App\Console\Commands\GenerateReports::class,
		\App\Console\Commands\SendNotifications::class,
		// on demand
		\App\Console\Commands\MatchRoute::class,
		\App\Console\Commands\ScrapeUrl::class,
		\App\Console\Commands\CacheMessages::class,
		\App\Console\Commands\CreateHub::class,
		\App\Console\Commands\CreateNotificationType::class,
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
		$schedule->command('alerts:send')->dailyAt('11:00');
		$schedule->command('gigs:notify')->everyFiveMinutes();
		$schedule->command('errors:send')->everyFiveMinutes();
		$schedule->command('posts:dispatch')->cron('*/2 * * * * *'); // every 2 minutes
		$schedule->command('posts:publish')->cron('*/2 * * * * *'); // every 2 minutes
		$schedule->command('notifications:send')->cron('*/2 * * * * *'); // every 2 minutes
		$schedule->command('reports:generate')->dailyAt('00:00');
		$schedule->command('social:cache-pages')->hourly();
		$schedule->command('social:cache-fields')->daily();
		$schedule->command('social:cache-connections')->everyFiveMinutes();
		$schedule->command('gigfeeds:load')->everyFiveMinutes();
		$schedule->command('files:cleanup')->hourly();
	}

	/**
	 * Register the Closure based commands for the application.
	 *
	 * @return void
	 */
	protected function commands()
	{
		require base_path('routes/console.php');
	}
}
