<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;
use App\Components\CommonTrait;

class NotificationSetting extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'notification_setting';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'type_id',
		'send_email', 
		'send_web', 
		'send_push', 
		'membership_id'
	];

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;
}
