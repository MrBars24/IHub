<?php

namespace App\Components;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait SluggableTrait
{
	/// Section: Properties

	/**
	 * The column to generate the slug from.
	 *
	 * @return string
	 */
	public function getSlugSource()
	{
		return 'name';
	}

	/**
	 * The column to save the slug into.
	 *
	 * @return string
	 */
	public function getSlugTarget()
	{
		return 'slug';
	}

	/// Section: Methods

	/**
	 * Generate and set slug for current model.
	 *
	 * @return Model The current model object
	 */
	public function setSlug()
	{
		$this->setAttribute($this->getSlugTarget(), Str::slug($this->getAttribute($this->getSlugSource())));
		return $this;
	}

	/// Section: Events

	/**
	 * Boot the trait for a model.
	 *
	 * @return void
	 */
	public static function bootSluggableTrait()
	{
		// attach model events for any model that inherits this trait
		// model event: SluggableTrait->creating
		static::creating(function($obj) {
			// set slug value if not set
			if(is_null($obj->getAttributeValue($obj->getSlugTarget()))) {
				$obj->setSlug();
			}
		});
	}
}