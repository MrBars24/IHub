<?php

namespace App\Events\Posts;

// App
use App\PostDispatchQueueItem;
use App\Post;
use App\User;

// Laravel
use Illuminate\Queue\SerializesModels;

class InstagramReady
{
	use SerializesModels;

	public $item;
	public $post;
	public $sharer;

	/**
	 * Create a new event instance.
	 * 
	 * @param \App\PostDispatchQueueItem $item
	 * @param \App\Post                  $post
	 * @param \App\User|\App\Hub         $sharer
	 * @return void
	 */
	public function __construct(PostDispatchQueueItem $item, Post $post, $sharer)
	{
		$this->item = $item;
		$this->post = $post;
		$this->sharer = $sharer;
	}
}