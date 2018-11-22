<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class PointAccrual extends Model
{
	// App
	use CommonTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'point_accrual';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['accrued_at'];

	/// Section: Properties

	const POINTS_POST_NEW = 5;

	const POINTS_POST_LIKE = 2;

	const POINTS_POST_COMMENT = 5;

	const POINTS_POST_SHARE = 5;

	/// Section: Relations

	public function target()
	{
		return $this->morphTo();
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

		// Model event: PointAccrual->creating
		self::creating(function($obj) {
			$obj->accrued_at = $obj->freshTimestamp();
		});
	}
}
