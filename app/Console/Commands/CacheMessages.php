<?php

namespace App\Console\Commands;

// App
use App\Post;
use App\Comment;
use App\Message;

// Laravel
use Illuminate\Console\Command;

class CacheMessages extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'messages:cache';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache messages for posts and comments';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// get ALL posts that are uncached
		$posts = Post::with([
			'hub'
		])
			->where(function($query) {
				$query->where('updated_at', '>', 'cached_at')
					->orWhereNull('message_cached');
			})
			->whereNotNull('message')
			->get();

		// get ALL comments that are uncached
		$comments = Comment::with([
			'post.hub'
		])
			->where(function($query) {
				$query->where('updated_at', '>', 'cached_at')
					->orWhereNull('message_cached');
			})
			->whereNotNull('message')
			->get();

		// get ALL messages that are uncached
		$messages = Message::with([
			'conversation.hub'
		])
			->where(function($query) {
				$query->where('updated_at', '>', 'cached_at')
					->orWhereNull('message_cached');
			})
			->whereNotNull('message')
			->get();

		// cache posts
		foreach($posts as $i => $item) {
			try {
				$cache = $item->message; // this will save cached message into $item->message_cached
				$item->save();
			} catch(\Exception $e) {
				vd($e->getTrace());
			}
		}

		// cache comments
		foreach($comments as $item) {
			try {
				$cache = $item->message; // this will save cached message into $item->message_cached
				$item->save();
			} catch(\Exception $e) {
				vd($e->getTrace());
			}
		}

		// cache messages
		foreach($messages as $item) {
			try {
				$cache = $item->message; // this will save cached message into $item->message_cached
				$item->save();
			} catch(\Exception $e) {
				vd($e->getTrace());
			}
		}
	}
}
