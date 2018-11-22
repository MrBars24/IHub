<?php

namespace App\Events\Notification;

// App
use App\PushNotificationQueueItem;

// Laravel
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPush implements ShouldQueue
{
	use SerializesModels;

	/**
	 * @var \App\PushNotificationQueueItem
	 */
	public $item;

	/**
	 * Create a new event instance.
	 *
	 * @return void
	 */

	public function __construct(PushNotificationQueueItem $item)
	{
		$this->item = $item->load(['user', 'notification']);
	}
}
