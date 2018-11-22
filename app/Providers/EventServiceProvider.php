<?php

namespace App\Providers;

// App
use App\Modules\Notifications\NotificationManager;

// Laravel
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
	/**
	 * The event listener mappings for the application.
	 *
	 * @var array
	 */
	protected $listen = [
		// socialite. See: http://socialiteproviders.github.io/#providers
		'SocialiteProviders\Manager\SocialiteWasCalled' => [
			'pinterest' => 'SocialiteProviders\Pinterest\PinterestExtendSocialite@handle',
			'youtube'   => 'SocialiteProviders\YouTube\YouTubeExtendSocialite@handle',
			'instagram' => 'SocialiteProviders\Instagram\InstagramExtendSocialite@handle',
		],
		'App\Events\Posts\InstagramReady' => [
			'App\Listeners\Posts\SendInstagramReadyNotification'
		],
		'App\Events\Posts\PostReported' => [
			'App\Listeners\Posts\SendPostReportedNotification'
		],
		'App\Events\Notification\SendPush' => [
			'App\Listeners\Notification\SendOneSignalNotification'
		],
	];

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function boot()
	{
		parent::boot();

		// event.gig.published
		// fired when a gig is published and visible on the site
		Event::listen('event.gig.published', function($event, $gig, $hub, $recipients) {

			// create notification
			// - recipients: all members in the hub
			// - sender: hub
			// - link: single gig page
			$nm = app(NotificationManager::class);
			
			// get sender
			$sender = $hub;
			
			// get notification info
			// $link = route('hub::gig.view', ['hub' => $hub->slug, 'gig' => $gig->id]);
			$url = url('/');
			$link = "{$url}/{$hub->slug}/gigs/{$gig->slug}";
			$summary = 'Gig notification';
			$message = 'The gig ' . $gig->title . ' is ready to be shared. Let\'s get cracking!';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.gig.expiring
		// fired when a gig is expiring within the next 8 hours
		Event::listen('event.gig.expiring', function($event, $gig, $hub, $recipients) {

			// create notification
			// - recipients: all members in the hub
			// - sender: hub
			// - link: single gig page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			// $link = route('hub::gig.view', ['hub' => $hub->slug, 'gig' => $gig->id]);
			$url = url('/');
			$link = "{$url}/{$hub->slug}/gigs/{$gig->slug}";
			$summary = 'Gig notification';
			$message = 'The gig ' . $gig->title . ' is expiring very soon. Let\'s get our hustle on!';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.gig.expired
		// fired when a gig is expired
		Event::listen('event.gig.expired', function($event, $gig, $hub, $recipients) {

			// create notification
			// - recipients: hub manager
			// - sender: hub
			// - link: edit gig page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			// $link = route('hub::gig.edit', ['hub' => $hub->slug, 'gig' => $gig->id]);
			// js route: /gig/edit/{gig}
			$url = url('/');
			$link = "{$url}/{$hub->slug}/gigs/edit/{$gig->slug}";
			$summary = 'Gig notification';
			$message = 'The gig ' . $gig->title . ' has expired';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.post.published
		// fired when a post is published and visible on the site (for scheduled posts only)
		Event::listen('event.post.published', function($event, $post, $hub, $recipients) {

			// create notification
			// - recipients: post author
			// - sender: hub
			// - link: newsfeed
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			// $link = route('hub::post.view', ['hub' => $hub->slug, 'post' => $post->id]);
			// js route: /{hub}/post/{post}
			$url = url('/');
			$link = "{$url}/{$hub->slug}/post/{$post->id}";
			$summary = 'Post notification';
			$message = 'Your post has been published';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.post.shared
		// fired when a post is shared by someone other than the post author
		Event::listen('event.post.shared', function($event, $post, $actor, $hub, $recipients) {

			// create notification
			// - recipients: post author
			// - sender: hub
			// - link: single post page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			$url = url('/');
			$link = "{$url}/{$hub->slug}/post/{$post->id}";
			$summary = 'Post notification';
			$message = $actor->name . ' has shared your post';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.post.liked
		// fired when a post is liked by someone other than the post author
		Event::listen('event.post.liked', function($event, $post, $actor, $hub, $recipients) {

			// create notification
			// - recipients: post author
			// - sender: hub
			// - link: single post page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			$url = url('/');
			$link = "{$url}/{$hub->slug}/post/{$post->id}";
			$summary = 'Post notification';
			$message = $actor->name . ' has liked your post';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.post.approved
		// fired when a gig post is approved by a hub manager (gig posts for gigs that require approval by hub manager)
		Event::listen('event.post.approved', function($event, $gig, $post, $hub, $recipients) {

			// create notification
			// - recipients: post author (influencer)
			// - sender: hub
			// - link: single post page @todo: need to think of a way to handle posts not yet published
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			$url = url('/');
			$link = "{$url}/{$hub->slug}/post/{$post->id}";
			$summary = 'Post notification';
			$message = 'Your post has been approved by the hub';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.comment.published
		// fired when a comment is made on a post by someone other than the post author
		Event::listen('event.comment.published', function($event, $comment, $post, $actor, $hub, $recipients) {

			// create notification
			// - recipients: post author
			// - sender: hub
			// - link: single post page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			// $link = route('hub::post.view', ['hub' => $hub->slug, 'post' => $post->id, 'comment' => $comment->id]);
			// todo: implement hash link for comments
			// $link should be "/{$hub->slug}/post/{$post->id}#{$comment->id or timestamp?}"
			$url = url('/');
			$link = "{$url}/{$hub->slug}/post/{$post->id}"; 

			$summary = 'Comment notification';
			$message = $actor->name . ' has replied to your post';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// event.membership.accepted
		// fired when a membership has been activated by an influencer
		Event::listen('event.membership.accepted', function($event, $membership, $influencer, $hub, $recipients) {

			// create notification
			// - recipients: hub manager
			// - sender: hub
			// - link: influencer's profile page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			// $link = route('hub::user.profile', ['hub' => $hub->slug, 'user' => $influencer->slug]);
			$url = url('/');
			$link = "{$url}/{$hub->slug}/{$influencer->slug}";
			$summary = 'User notification';
			$message = $influencer->name . ' has accepted your hub membership invite';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});

		// @todo: not sure how to handle this one yet
		// event.membership.removed
		// fired when a membership has been activated by an influencer
		/*Event::listen('event.membership.removed', function($event, $membership, $influencer, $hub, $recipients) {

			// create notification
			// - recipients: hub manager
			// - sender: hub
			// - link: influencer's profile page
			$nm = app(NotificationManager::class);

			// get sender
			$sender = $hub;

			// get notification info
			$link = 'http://localhost';
			$summary = 'Gig notification';
			$message = 'In rhoncus nulla quis nulla bibendum, http://twitter.com id porta augue viverra.';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});*/

		// event.message.created
		// fired when a private message is created and sent to someone
		Event::listen('event.message.created', function($event, $conversation, $sender, $hub, $recipients) {

			// create notification
			// - recipients: message recipient
			// - sender: message sender
			// - link: single conversation page
			$nm = app(NotificationManager::class);

			// get notification info
			// $link = route('hub::message.conversation', ['hub' => $hub->slug, 'conversation' => $conversation->id]);
			$url = url('/');
			$link = "{$url}/{$hub->slug}/message/{$conversation->id}";
			$summary = 'Message notification';
			$message = $sender->name . ' has sent you a message';

			// send notifications
			$nm->notify($event, $recipients, $sender, $link, $summary, $message, ['hub' => $hub]);
		});
	}
}
