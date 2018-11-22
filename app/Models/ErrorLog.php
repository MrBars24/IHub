<?php

namespace App;

// Laravel
use Illuminate\Database\Eloquent\Model;

class ErrorLog extends Model
{
	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'error_log';
}
