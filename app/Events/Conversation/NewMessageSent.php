<?php

namespace App\Events\Conversation;

// App
use App\Conversation;

// Laravel
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewMessageSent implements ShouldBroadcastNow
{
	use InteractsWithSockets, SerializesModels;

	/**
	 * @var \App\Conversation
	 */
	public $conversation;

	/**
	 * Create a new event instance.
	 *
	 * @param \App\Conversation $conversation
	 */
	public function __construct(Conversation $conversation)
	{
		$this->conversation = $conversation;
		$this->dontBroadcastToCurrentUser();
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('ConversationNew.' . $this->conversation->receiver_id);
	}

	/**
	 * Get the data that should be sent with the broadcasted event.
	 *
	 * @return array
	 */
	public function broadcastWith()
	{
		return $this->conversation->load([
			'messages',
			'sender',
			'receiver'
		])->toArray();
	}
}
