<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class TwitterConnection extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'twitter_connection';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['native_id', 'profile_id', 'screen_name', 'display_name', 'type', 'avatar', 'access_token', 'follower_count', 'end_point_type', 'is_active'];
}
