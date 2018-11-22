<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;

class Category extends Model// implements TaxonomyInterface
{
	// App
	use CommonTrait;
	//use TaxonomyTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'category';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['name', 'hub_id', 'is_active'];

	/// Section: Relations

	public function items()
	{
		return $this->hasMany(Gig::class);
	}
}
