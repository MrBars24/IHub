<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\FileContextInterface;
use App\Components\FileContextTrait;
use App\Components\MessageInterface;
use App\Components\MessageTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Post extends Model implements FileContextInterface, MessageInterface
{
	// App
	use CommonTrait, FileContextTrait, MessageTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'hub_id',
		'message'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'cached_at',
	];

	/**
	 * the attributes that should be included for the json|array calls
	 * 
	 * @var array
	 */
	protected $appends = [
		'message_raw',
		'message_plain' // and message_plain? not sure
	];

	/// Section: Relation

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function author()
	{
		return $this->morphTo();
	}

	public function attachment()
	{
		return $this->hasOne(PostAttachment::class);
	}

	public function comments()
	{
		return $this->hasMany(Comment::class);
	}

	public function likes()
	{
		return $this->morphMany(Like::class, 'content');
	}

	public function reports()
	{
		return $this->hasMany(PostReport::class);
	}

	public function hiddenPosts()
	{
		return $this->hasMany(PostHide::class);
	}

	public function likedByUser()
	{
		return $this->hasOne(Like::class);
	}

	public function shares()
	{
		return $this->hasMany(PostDispatchQueueItem::class);
	}

	public function subPosts()
	{
		return $this->hasMany(PostDispatchQueueItem::class);
	}

	/// Section: Mutators

	public function getLikedAttribute()
	{
		if($this->relationLoaded('like') && !is_null($this->like)) {
			return $this->like->is_liked;
		}
		return false;
	}

	/// Section: Methods

	public function unPublished()
	{
		$this->is_published = false;
		$this->save();
	}

	public static function withComponents()
	{
		return [
			'author', // select and order by calls don't work on polymorphic relations
			'likes' => function($query) {
				$query->leftJoin('entity', function ($join) {
					$join->on('like.liker_id', '=', 'entity.entity_id')
						->on('like.liker_type', '=', 'entity.entity_type');
				})
					->where('like.is_liked', '=', true)
					->orderBy('like.reliked_at', 'DESC')
					->orderBy('like.created_at', 'DESC');
			},
			'comments' => function($query) {
				$query->where('is_published', '=', true)
					->orderBy('created_at', 'ASC');
			},
			'comments.author', // select and order by calls don't work on polymorphic relations
			'comments.likes' => function($query) {
				$query->leftJoin('entity', function ($join) {
					$join->on('like.liker_id', '=', 'entity.entity_id')
						->on('like.liker_type', '=', 'entity.entity_type');
				})
					->where('like.is_liked', '=', true)
					->orderBy('like.reliked_at', 'DESC')
					->orderBy('like.created_at', 'DESC');
			},
			'attachment.file',
			'shares' => function($query) {
				$query->whereIn('post_dispatch_queue.result', [
					'success', 
					'pending'
				])
				->orderBy('finished_at', 'DESC');
			}
		];
	}

	/// Section: FileContextTrait

	/**
	 * The prefix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextPrefix()
	{
		return 'p';
	}
}
