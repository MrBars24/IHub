<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\MessageInterface;
use App\Components\MessageTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Comment extends Model implements MessageInterface
{
	// App
	use CommonTrait, MessageTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'comment';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['message'];

	/// Section: Relations

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function post()
	{
		return $this->belongsTo(Post::class);
	}

	public function author()
	{
		return $this->morphTo();
	}

	public function likes()
	{
		return $this->morphMany(Like::class, 'content');
	}

	/// Section: MessageTrait

	protected $supportInternalTagging = true;
}
