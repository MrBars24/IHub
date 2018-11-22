<?php

namespace App\Components;

trait CommonTrait
{
	/// Section: Mutators

	public function getObjectClassAttribute()
	{
		return join('', array_slice(explode('\\', get_class($this)), -1));
	}

	/// Section: Methods

	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		// add extra attributes
		$this->registerVisibleAttribute('object_class');
		$this->syncOriginalAttribute('object_class'); // @todo this should be put in registerVisibleAttribute and then check if dirty or not, if not dirty, then call syncOriginalAttribute

		// do array conversion
		$attributes = parent::toArray();

		return $attributes;
	}

	/**
	 * Register visible attribute to this object
	 *
	 * @param string $key
	 */
	public function registerVisibleAttribute($key)
	{
		if(!empty($this->visible) && !isset($this->visible[$key])) {
			$this->visible[] = $key;
		}
		$this->setAttribute($key, $this->$key);
	}

	/// Section: Events

	/**
	 * Register a booted model event with the dispatcher.
	 *
	 * @param  \Closure|string  $callback
	 * @param  integer          $priority
	 * @return void
	 */
	public static function booted($callback, $priority = 0)
	{
		static::registerModelEvent('booted', $callback, $priority);
	}
}