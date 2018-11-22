<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PinterestAccess extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'pinterest_access';
}
