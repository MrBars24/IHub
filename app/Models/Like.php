<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'like';

	/**
	 * The attributes that should be visible in arrays.
	 *
	 * @var array
	 */
	protected $visible = ['is_liked', 'liked_at', 'content', 'liker', 'entity_id', 'entity_name', 'entity_type'];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [
		'liked_at',
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['is_liked'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'unliked_at',
		'reliked_at',
		'liked_at',
	];

	/// Section: Relations

	public function liker()
	{
		return $this->morphTo();
	}

	public function content()
	{
		return $this->morphTo();
	}

	/// Section: Mutators

	public function getLikedAtAttribute()
	{
		$date = $this->getAttribute('reliked_at') ?: $this->getAttribute('created_at');
		return $date->format('Y-m-d H:i:s');
	}
}
