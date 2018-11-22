<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;
use App\Components\CommonTrait;

class LoginHistory extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'login_history';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['user_id', 'oauth_token', 'device_token', 'device_os', 'ip_address', 'user_agent'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
	];
}
