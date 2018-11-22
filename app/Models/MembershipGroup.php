<?php

namespace App;

// App
use App\Components\TaxonomyInterface;

// Laravel
use Illuminate\Database\Eloquent\Model;

class MembershipGroup extends Model implements TaxonomyInterface
{
	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'membership_group';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'multiplier'];

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/// Section: Relations

	public function memberships()
	{
		return $this->hasMany(Membership::class);
	}
}
