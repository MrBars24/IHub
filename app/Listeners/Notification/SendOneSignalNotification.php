<?php

namespace App\Listeners\Notification;

// App
use App\Events\Notification\SendPush;

// Laravel
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

// 3rd Party
use OneSignal;

class SendOneSignalNotification
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
	 * @param  SendPush  $event
	 * @return void
	 */
	public function handle(SendPush $event)
	{
		$item = $event->item;
		$user = $item->user;
		$notification = $item->notification;

		// query login history
		// - latest one
		$loginHistory = $user->login_histories()
			->whereNotNull('device_token')
			->latest()
			->first();

		// fix the url to open the app when the notification was clicked.
		// current url: https://{domain}/{hub_slug}/some-route 
		// should be: influencerhub://app-redirect?url=/{hubslug}/some-route
		// TODO: i think influencerhub URL scheme should be placed in .env. 

		// user must have a device_token stored in login_history.
		if (!is_null($loginHistory)) {
			$url = str_replace(url('/'), 'influencerhub://app-redirect?url=', $notification->link);

			// send push notification
			OneSignal::addParams([
				'headings' => [
					'en' => $notification->summary
				],
				'ios_badgeType' => 'Increase',
				'ios_badgeCount' => 1,
				'android_group' => $notification->summary,
				'android_group_message' => [
					'en' => 'You have ${notif_count} new ' . $notification->summary . '.'
				]
			])
			->sendNotificationUsingTags($notification->message, [
				[
					'field' => 'tag',
					'key' => 'email',
					'relation' => '=',
					'value' => $user->email
				]
			], $url, $notification);
		}
	}
}
