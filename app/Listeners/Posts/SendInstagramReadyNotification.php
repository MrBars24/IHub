<?php

namespace App\Listeners\Posts;

// App
use App\Events\Posts\InstagramReady;
use App\Modules\Notifications\NotificationManager;

// Laravel
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendInstagramReadyNotification
{
	/**
	 * Create the event listener.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Handle the event.
	 *
	 * @param  InstagramReady  $event
	 * @return void
	 */
	public function handle(InstagramReady $event)
	{
		// create notification
		// - recipients: authenticated user.
		// - sender: hub
		// - link: single post page
		$nm = app(NotificationManager::class);

		$post = $event->post;
		$item = $event->item;
		$hub = $item->hub;

		$notificationType = 'event.instagram.ready';

		$sender = $hub;
		$recipients = $event->sharer;

		// get notification info
		$link = url('/') . "/{$hub->slug}/post/{$post->id}/instagram/{$item->id}";
		// $link = route('hub::posts.instagram', ['hub' => $hub->slug, 'post' => $event->post->id, 'item' => $event->item->id]);

		$summary = 'Post notification';
		$message = trim(mb_strimwidth($event->post->message, 0, 30, '..'));
		$message = "Your post $message is ready to share to Instagram.";

		$nm->notify($notificationType, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
	}
}
