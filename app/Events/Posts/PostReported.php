<?php

namespace App\Events\Posts;

// App
use App\Post;
use App\PostReport;

// Laravel
use Illuminate\Queue\SerializesModels;

class PostReported
{
	use SerializesModels;

	public $post;
	public $report;

	/**
	 * Create a new event instance.
	 * 
	 * @param \App\Post $post
	 * @return void
	 */
	public function __construct(Post $post, PostReport $report)
	{
		$this->post = $post;
		$this->report = $report;
	}
}
