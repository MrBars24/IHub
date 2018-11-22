<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PushNotificationQueueItem extends Model
{
	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'push_notification_queue';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [];

	/**
	 * The attributes that should be mutated to dates.
	 * 
	 * @var array
	 */
	protected $dates = [
		'started_at',
		'finished_at',
		'updated_at',
		'deleted_at'
	];

	/// Section: Relations

	public function notification()
	{
		return $this->belongsTo(Notification::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
