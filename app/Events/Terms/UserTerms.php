<?php

namespace App\Events\Terms;

// App
use App\Membership;
use App\Hub;

// Laravel
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UserTerms implements ShouldBroadcastNow
{
	use InteractsWithSockets, SerializesModels;
 
	/**
	 * @var \App\PushNotificationQueueItem
	 */
	public $item;

	/**
	 * Create a new event instance.
	 *
	 * @param \App\Hub $hub
	 * @param array    $item
	 * @return void
	 */

	public function __construct(Hub $hub, $item)
	{
		$this->hub = $hub;
		$this->item = $item;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new Channel('User.Terms');
	}

	/**
	 * Get the data that should be sent with the broadcasted event.
	 *
	 * @return array
	 */
	public function broadcastWith()
	{
		return $this->item->toArray();
	}
}