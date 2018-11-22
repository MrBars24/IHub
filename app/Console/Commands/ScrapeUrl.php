<?php

namespace App\Console\Commands;

// App
use App\Modules\Urls\UrlManager;
use App\PostAttachment;

// Laravel
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ScrapeUrl extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'url:scrape
	                       {url : The URL to scrape}';
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scrape supplied URL and output data';

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['url', null, InputArgument::REQUIRED, 'The URL to scrape']
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

		// scrape
		$info = app(UrlManager::class)->scrape($url);

		// display info
		// https://github.com/oscarotero/Embed

		// test url's
		// - image
		// - video
		// - 404
		// - link
		// - news article (that requires cookies)

		$this->info('-------------------------------------------------------');
		$this->info('analysing url: ' . $url);
		$this->info('-------------------------------------------------------');
		foreach($info->getAttributes() as $key => $value) {
			$this->info($key);
			$this->info('    ' . $value);
		}
		$this->info('-------------------------------------------------------');

		// process to attachment
		$attachment = PostAttachment::createFromScrape($info);

		$this->info('-------------------------------------------------------');
		$this->info('process attachment:');
		$this->info('-------------------------------------------------------');
		foreach($attachment->getAttributes() as $key => $value) {
			$this->info($key);
			$this->info('    ' . $value);
		}
		$this->info('-------------------------------------------------------');
	}
}
