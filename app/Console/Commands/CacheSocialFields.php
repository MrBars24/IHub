<?php

namespace App\Console\Commands;

// App
use App\LinkedAccount;
use App\YoutubeCategory;

// Laravel
use Illuminate\Console\Command;

class CacheSocialFields extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'social:cache-fields';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache social fields for various platforms';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->handleYouTubeCategories();
	}

	/**
	 * Handle YouTube categories
	 */
	private function handleYouTubeCategories()
	{
		// keep track of ids to exclude from disable query
		$exclude = [];

		// get categories
		$categories = LinkedAccount::queryYouTubeCategories();

		// store data
		foreach($categories as $category) {
			$store = YoutubeCategory::firstOrNew([
				'native_id' => $category->native_id
			]);
			$store->fill([
				'native_id' => $category->native_id,
				'title'     => $category->title,
			   'is_active' => true,
			]);
			$exists = $store->exists;
			$store->save();

			$exclude[] = $store->id;

			// cached row info
			$this->info('Cached ' . YoutubeCategory::class . ' "' . $category->title . '" (' . $category->native_id . ') using ' . (!$exists ? 'insert' : 'update'));
		}

		// disable any previously cached results that were missed in the data above
		$count = YoutubeCategory::query()
			->whereNotIn('id', $exclude)
			->update([
				'is_active' => false
			]);

		// disable count info
		$this->info('Disabled ' . $count . ' ' . YoutubeCategory::class . ' records that were missed in the api query');
	}
}
