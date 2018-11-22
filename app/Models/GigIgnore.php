<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class GigIgnore extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'gig_ignore';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'gig_id', 'user_id'
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
}
