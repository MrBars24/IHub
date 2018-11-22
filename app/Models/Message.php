<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\MessageInterface;
use App\Components\MessageTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Message extends Model implements MessageInterface
{
	// App
	use CommonTrait, MessageTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'message';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'message',
		'receiver_id',
		'receiver_type'
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
		'read_at',
	];

	/// Section: Relations

	public function sender()
	{
		return $this->morphTo();
	}

	public function conversation()
	{
		return $this->belongsTo(Conversation::class);
	}

	/// Section: Events

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		// Model event: Message->created
		self::created(function($obj) {
			$conversation = $obj->conversation;
			$conversation->last_message_id = $obj->id;
			$conversation->save();
		});
	}
}
