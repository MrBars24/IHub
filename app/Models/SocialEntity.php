<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class SocialEntity extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'social_entity';

	/// Section: Methods

	/// Section: Post Dispatch

	public function dispatchPost($post, $sharer, $context, $params, $attachment)
	{
		// implement: facebook, twitter, etc....
		switch($this->platform) {
			case 'facebook':
			case 'twitter':
			case 'linkedin':
			case 'pinterest':
			case 'youtube':
				$this->queueJob($post, $sharer, $this->platform, $context, $params, 'pending', $attachment);
				break;
			case 'instagram':
				$this->handleInstagram($post, $sharer, $context, $params, 'pending', $attachment); // we'll now queue instagram jobs too
				break;
			default: // other
				break;
		}
		return;
	}

	public function preparePost($post, $sharer, $context, $params, $attachment)
	{
		// implement: facebook, twitter, etc....
		switch($this->platform) {
			case 'facebook':
			case 'twitter':
			case 'linkedin':
			case 'pinterest':
			case 'youtube':
				$this->queueJob($post, $sharer, $this->platform, $context, $params, 'not ready', $attachment);
				break;
			case 'instagram':
				$this->handleInstagram($post, $sharer, $context, $params, 'not ready', $attachment); // we'll now queue instagram jobs too
				break;
			default: // other
				break;
		}
		return;
	}

	private function handleInstagram($post, $sharer, $context, $params, $status, $attachment)
	{
		// create queue item
		$item = $this->queueJob($post, $sharer, $this->platform, $context, $params, $status, $attachment);
	}

	private function queueJob($post, $sharer, $platform, $context, $params, $status, $attachment)
	{
		return PostDispatchQueueItem::queue($post, $this, $sharer, $platform, $context, $params, $status, $attachment);
	}
}
