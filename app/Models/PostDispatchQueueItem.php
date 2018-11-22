<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PostDispatchQueueItem extends Model
{
	// App
	use CommonTrait;

	// App
	//use PlatformTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_dispatch_queue';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/// Section: Relations

	public function job()
	{
		return $this->belongsTo(PostDispatchJob::class);
	}

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function post()
	{
		return $this->belongsTo(Post::class);
	}

	public function attachment()
	{
		return $this->belongsTo(PostAttachment::class);
	}

	public function social()
	{
		return $this->belongsTo(SocialEntity::class, 'native_id', 'native_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/// Section: Mutators

	public function getParamsAttribute()
	{
		return !is_null($this->attributes['params']) ? json_decode($this->attributes['params'], true) : [];
	}

	/// Section: Methods

	/**
	 * Queue post dispatch items to prepare for social media send off
	 *
	 * @param  \App\Post                $post       The parent post
	 * @param  \App\SocialEntity        $social     The social entity of this post
	 * @param  \App\User                $sharer     The social entity of this post
	 * @param  string                   $platform   Name of the platform
	 * @param  string                   $context    Where the post was made from (newsfeed, share, gig)
	 * @param  array                    $params     The parameters to assign this item (eg: youtube category and title)
	 * @param  string                   $status     The initial status of the queue item
	 * @param  null|\App\PostAttachment $attachment The post attachment, some post don't need to have an attachment to be published.
	 * @return static
	 */
	public static function queue(Post $post, SocialEntity $social, User $sharer, $platform, $context, $params, $status = 'pending', $attachment = null)
	{
		$item = new static;
		$item->hub()->associate($post->hub);
		$item->post()->associate($post);
		$item->attachment()->associate($attachment);
		$item->social()->associate($social);
		$item->user()->associate($sharer);
		$item->platform = $platform;
		$item->context = $context;
		$item->result = $status; // usually 'pending'
		$item->params = !empty($params) ? json_encode($params) : null;
		$item->message = $post->message_plain;
		$item->save();

		return $item;
	}
}
