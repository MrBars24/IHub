<?php

namespace App\Console\Commands;

// App
use App\GigPost;

// Laravel
use Illuminate\Console\Command;

class PublishPosts extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'posts:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish posts and get them ready for social media';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$now = carbon();

		// get ALL post schedule items that have not been successfully processed
		$queue = GigPost::with([
			'post.hub',
			'post.author'
		])
			->where('schedule_result', '=', 'pending')
			->where('status', '=', 'scheduled')
			->where('schedule_at', '<=', $now) // check whether schedule_at has elapsed
			->get();

		// process all items
		foreach($queue as $i => $item) {
			$item->schedule_result = 'started';
			$item->save();

			// publish
			$item->publish();

			// finish
			$item->schedule_result = 'success';
			$item->save();
		}
	}
}
