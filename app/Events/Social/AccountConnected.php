<?php

namespace App\Events\Social;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AccountConnected implements ShouldBroadcastNow
{
	use InteractsWithSockets, SerializesModels;

	public $account;

	/**
	 * Create a new event instance.
	 *
	 * @param \App\LinkedAccount $account
	 * @return void
	 */
	public function __construct($account)
	{
		$this->account = $account;
	}

	/**
	 * Get the channels the event should broadcast on.
	 *
	 * @return Channel|array
	 */
	public function broadcastOn()
	{
		return new PrivateChannel('SocialAccount.' . $this->account->user_id);
	}

	public function broadcastWith()
	{
		$account = $this->account;

		return [
			'account' => $account,
			'message' => "Your {$account->platform} account is now connected to Influencer HUB."
		];
	}
}
