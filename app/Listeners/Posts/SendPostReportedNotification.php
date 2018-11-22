<?php

namespace App\Listeners\Posts;

// App
use App\Events\Posts\PostReported;
use App\Modules\Notifications\NotificationManager;

// Laravel
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPostReportedNotification implements ShouldQueue
{

	use InteractsWithQueue;

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
	 * @param  PostReported  $event
	 * @return void
	 */
	public function handle(PostReported $event)
	{
		// create notification
		// - recipients: hubmanager.
		// - sender: hub ? or the influencer who reported the post?..
		// - link: single post page
		$nm = app(NotificationManager::class);

		$post = $event->post->load('hub');
		$report = $event->report;
		$hub = $post->hub;

		$notificationType = 'event.post.reported';

		$sender = $hub;
		$recipients = $hub;

		// get notification info
		$link = url('/') . "/{$hub->slug}/post/{$post->id}";
		// $link = route('hub::posts.instagram', ['hub' => $hub->slug, 'post' => $event->post->id]);

		$summary = 'Post notification';
		$message = trim(mb_strimwidth($event->post->message, 0, 30, '..'));
		$message = "The post $message has been reported.";

		$nm->notify($notificationType, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
	}
}
