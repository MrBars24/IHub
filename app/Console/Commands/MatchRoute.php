<?php

namespace App\Console\Commands;

// Laravel
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class MatchRoute extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'route:match {url}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Match supplied URL to a route';

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['url', null, InputArgument::REQUIRED, 'The URL to match']
		];
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// params
		$url = $this->argument('url');

		// get routes
		$route = app('router')->getRoutes()->match(app('request')->create($url));

		// get info
		$name   = $route->getName();
		$action = $route->getActionName();
		$params = $route->parameters();

		// display: alias, controller action, params
		$this->comment($name);
		$this->comment($action);
		$this->comment(json_encode($params));
	}
}
