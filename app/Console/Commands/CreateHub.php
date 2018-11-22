<?php

namespace App\Console\Commands;

// App
use App\Hub;

// Laravel
use Illuminate\Console\Command;

class CreateHub extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'hubs:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new hub';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// input
		$name       = $this->ask('Hub: Name (eg: My Hub)');
		$slug       = $this->ask('Hub: Slug (eg: my-hub)', str_slug($name));
		$filesystem = $this->ask('Hub: Filesystem (eg: my-hub)', str_slug($name));
		$summary    = $this->ask('Hub: Summary (eg: This is my summary)');
		$email      = $this->ask('Hub Manager: Email (eg: hubmanager@company.com)');

		// summary
		$this->info('Hub name: ' . $name);
		$this->info('Hub slug: ' . $slug);
		$this->info('Hub filesystem: ' . $filesystem);
		$this->info('Hub summary: ' . $summary);
		$this->info('Hub manager email: ' . $email);

		// confirm?
		if(!$this->confirm('Confirm? [y|N]')) {
			die;
		}

		// create hub
		try {
			$hub = Hub::seedFromDefault($name, $slug, $filesystem, $summary, $email);
			$this->info('Hub "' . $hub->name . '" with id "' . $hub->id . '" created.');
		} catch(\Exception $e) {
			$this->info('Error creating hub: ' . $e->getMessage());
			$this->info('-------------------');
			$this->info($e->getTraceAsString());
		}
	}
}
