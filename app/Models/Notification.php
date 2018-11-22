<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\MessageInterface;
use App\Components\MessageTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Notification extends Model implements MessageInterface
{
	// App
	use CommonTrait, MessageTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'notification';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'cached_at',
		'read_at',
	];

	/// Section: Relations

	public function type()
	{
		return $this->belongsTo(NotificationType::class);
	}

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function receiver()
	{
		return $this->morphTo();
	}

	public function sender()
	{
		return $this->morphTo();
	}
}
