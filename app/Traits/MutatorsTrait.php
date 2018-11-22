<?php
namespace App\Traits;

trait MutatorsTrait
{
	/// Section: Mutators

	public function getNamespaceAttribute()
	{
		return array_slice(explode('\\', get_class($this)), 0, -1)[0];
	}

	public function getClassnameAttribute()
	{
		return join('', array_slice(explode('\\', get_class($this)), -1));
	}

	/// Section: Methods

	/**
	 * Dynamically retrieve attributes on the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function __get($key)
	{
		$value = null;
		if(method_exists($this, 'formatFileContext')) {
			$value = $this->formatFileContext($key);
		}
		if(method_exists($this, 'formatTimestampable')) {
			$value = $this->formatTimestampable($key);
		}

		if(!is_null($value)) {
			return $value;
		}
		return $this->getAttribute($key);
	}

	/**
	 * Convert the model instance to an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		// mutate attributes specified in the class
		if(property_exists($this, 'mutate') && is_array($this->mutate)) {
			//$this->mutate[] = 'namespace';
			$this->mutate[] = 'exists';
			$this->mutate[] = 'classname';
			$this->mutate($this->mutate);
		}

		// do array conversion
		$attributes = parent::toArray();

		return $attributes;
	}

	public function mutate($mutate)
	{
		if(is_array($mutate)) {
			foreach($mutate as $key) {
				$this->setAttribute($key, $this->$key); // will execute mutator
				if(!empty($this->visible)) {
					$this->visible[] = $key; // add attribute to 'visible' array
				}
			}
		}
	}
}