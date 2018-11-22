<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PostHide extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_hide';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'post_id', 'user_id', 'is_hidden'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'unhid_at',
		'rehid_at',
	];

	/// Section: Relation

	public function post()
	{
		return $this->belongsTo(Post::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
