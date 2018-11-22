<?php

namespace App\Console\Commands;

// Laravel
use Illuminate\Console\Command;

// App
use App\GigFeed;
use App\GigFeedPost;
use App\Modules\Files\FileManager;

// 3rd party
use Image;
class LoadGigFeeds extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'gigfeeds:load';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Load the gig feeds configured from the gig_feed table';

	public function __construct()
	{
		parent::__construct();

		$rootDir = dirname(dirname(dirname(__DIR__))); // root directory
		$libPath = $rootDir . '/library/rss-bridge/';

		// set bridge and formatter directory
		\Bridge::setDir($rootDir . '/app/Modules/RssBridge/Bridges/');
		\Format::setDir($libPath . 'formats/');
		\Cache::setDir($libPath . 'caches/');
	}
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// get the list of all gig feeds in the app
		$gigFeeds = GigFeed::query()
			->where('is_active', '=', true)
			->whereNotNull('evaluated_url') // just to make sure that the evaluated_url is set
			->get();
		
		foreach($gigFeeds as $feed) {
			// set status to stared
			$feed->last_result = 'started';
			$feed->last_error = null;

			$format = \Format::create('Json');
			$bridgeClass = $this->getBridgeClass($feed->type);
			$bridge = \Bridge::create($bridgeClass);
			$url_profile = null; // let's set the url_profile null for now for the rssfeed links

			// get the relevant bridge class (depending on gig feed type)
			try {
				// if it's rssfeed type, use the xml parser
				if ($feed->type === 'rssfeed') {
					$this->comment('Fetching from ' . $feed->source_url);
					$bridge->parseData($feed->source_url);
				}
				else {
					$bridge->setDatas($feed->evaluated_url_data);
					$this->comment('Fetching from ' . $bridge->getURI());
					$url_profile = $bridge->getURI();
				}

				// get data
				$format->setItems($bridge->getItems())
					->setExtraInfos($bridge->getExtraInfos());

				// convert data to gig_feed_post records
				$this->handleFetchedItems($format->getItems(), $feed, $url_profile);

				// set status to success
				$feed->last_result = 'success';
			}
			catch(\Exception $e) {
				$this->error('Error fetching using the ' . $bridgeClass . ': ' . $e->getMessage());
				$feed->last_result = 'error';
				$feed->last_error = 'error: ' . $e->getMessage() . "\n" . $e->getTraceAsString();
			}
			
			// save outcome to gig feed
			$feed->save();
		}
	}

	/**
	 * Create gig feed posts from rss data
	 *
	 * @param array       $items
	 * @param App\GigFeed $feed
	 * @param string      $url_profile
	 * @return void
	 */
	private function handleFetchedItems($items, $feed, $url_profile)
	{
		$hard_limit_days = carbon()->subDays($feed->hard_limit_days);
		foreach($items as $item) {
			$published_date = isset($item['timestamp']) ? carbon()->createFromTimestamp($item['timestamp']) : null;

			// check: ignore if the item published date is past the feed->hard_limit_days
			if (!is_null($published_date) && $published_date->lt($hard_limit_days)) {
				continue;
			}

			// find existing post or create a new post if the post doesn't exist
			$feedPost = GigFeedPost::firstOrCreate([
				'hub_id' => $feed->hub_id,
				'link' => $item['uri'],
				'type' => $feed->type,
				'url_profile' => $url_profile
			]);

			// populate post
			$feedPost->originally_published_at = $published_date;
			$feedPost->native_id = isset($item['id']) ? $item['id'] : null;
			$feedPost->link = $item['uri'];
			$feedPost->title = isset($item['author']) ? $item['author'] : ''; // i think the title should be the authors name..
			$feedPost->description = isset($item['description']) ? $item['description'] : '';

			// attachments
			// important: must only copy the file if no file storage for this item
			if(isset($item['enclosures']) && count($item['enclosures']) && is_null($feedPost->file)) {
				$filename = $item['enclosures'][0];
				$image = Image::make($filename);
				// stage file
				$file = app(FileManager::class)->stage($image);
				$filename = $feedPost->storeFile($file['path']);

				$feedPost->thumbnail = $filename;
			}
			$feedPost->save();
		}
	}

	/**
	 * Map gig feed type to its bridge php class
	 *
	 * @param $type
	 * @return string
	 */
	private function getBridgeClass($type)
	{
		switch($type) {
			case 'facebookpage': 
				$bridge = 'Facebook'; break;
			case 'twitteraccount': 
				$bridge = 'Twitter'; break;
			case 'instagramaccount': 
				$bridge = 'Instagram'; break;
			case 'pinterestboard': 
				$bridge = 'Pinterest'; break;
			case 'googlepluspage': 
				$bridge = 'GooglePlus'; break;
			case 'linkedincompany':
				$bridge = 'Linkedin'; break;
			default: 
				$bridge = 'Rss';
				break;
		}
		return $bridge;
	}
}
