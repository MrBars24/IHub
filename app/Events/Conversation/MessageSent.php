<?php

namespace App\Events\Conversation;

// App
use App\Message;

// Laravel
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MessageSent implements ShouldBroadcastNow
{
	use InteractsWithSockets, SerializesModels;

	/**
	 * @var \App\Message
	 */
	public $message;

	/**
	 * Create a new event instance.
	 *
	 * @param \App\Conversation $conversation
	 */
	public function __construct(Message $message)
	{
		$this->message = $message;
		$this->dontBroadcastToCurrentUser();
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('Conversation.' . $this->message->conversation_id);
	}

	/**
	 * Get the data that should be sent with the broadcasted event.
	 *
	 * @return array
	 */
	public function broadcastWith()
	{
		return $this->message->load(['sender'])->toArray();
	}
}
