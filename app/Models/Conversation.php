<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'conversation';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'receiver_id',
		'receiver_type',
		'hub_id',
		'sender_id',
		'sender_type',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
	];

	/// Section: Relations

	public function hub()
	{
		return $this->belongsTo(Hub::class);
	}

	public function messages()
	{
		return $this->hasMany(Message::class);
	}

	public function lastMessage()
	{
		return $this->hasOne(Message::class, 'id', 'last_message_id');
	}

	public function receiver()
	{
		return $this->morphTo();
	}

	public function sender()
	{
		return $this->morphTo();
	}

	// check if the conversation can be access by the entities
	public function canAccessBy($entityIds)
	{
		return (
				in_array($this->sender_id, $entityIds) && 
				($this->sender_type == Hub::class || $this->sender_type == User::class)
			) ||
			(
				in_array($this->receiver_id, $entityIds) && 
				($this->receiver_type == Hub::class || $this->receiver_type == User::class)
			);

		// return $this->query()
		// ->where(function($query) use ($entityIds) {
		// 	$query->whereIn('sender_id', $entityIds)
		// 		->where(function($query2) {
		// 			$query2->where('sender_type', '=', User::class)
		// 				->orWhere('sender_type', '=', Hub::class);
		// 		});
		// })
		// ->orWhere(function($query) use ($entityIds) {
		// 	$query->whereIn('receiver_id', $entityIds)
		// 		->where(function($query2) {
		// 			$query2->where('receiver_type', '=', User::class)
		// 				->orWhere('receiver_type', '=', Hub::class);
		// 		});
		// })
		// ->where('id', '=', $conversationId)
		// ->first();
	}
}
