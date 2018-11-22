<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;
use App\Components\CommonTrait;

class Platform extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'platform';

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
