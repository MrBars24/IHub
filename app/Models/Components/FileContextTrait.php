<?php

namespace App\Components;

trait FileContextTrait
{
	/// Section: Properties

	/**
	 * The column to identify filesystem directory for the file context.
	 *
	 * @return string
	 */
	public function getFileContextColumn()
	{
		return 'filesystem';
	}

	/**
	 * The prefix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextPrefix()
	{
		return '_';
	}

	/**
	 * The suffix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextSuffix()
	{
		return '';
	}

	/**
	 * The total length that the file context should be
	 *
	 * @return string
	 */
	public function getFileContextLength()
	{
		return 24;
	}

	/// Section: Methods

	/**
	 * Generate the directory name for this object
	 */
	public function generateFilePath()
	{
		$prefix = $this->getFileContextPrefix();
		$suffix = $this->getFileContextSuffix();
		$length = $this->getFileContextLength() - strlen($prefix) - strlen($suffix);
		$length = max($length, 3);
		$filesystem = $prefix . str_random($length) . $suffix;
		$this->setAttribute($this->getFileContextColumn(), $filesystem);
	}

	/**
	 * Get the directory name for this object
	 */
	public function getFilePath()
	{
		return $this->getAttribute($this->getFileContextColumn());
	}

	/// Section: Events

	/**
	 * Boot the trait for a model.
	 *
	 * @return void
	 */
	public static function bootFileContextTrait()
	{
		// attach model events for any model that inherits this trait
		// model event: FileContextTrait->creating
		static::creating(function($obj) {

			// Set slug value if not set
			if(is_null($obj->getAttribute($obj->getFileContextColumn()))) {
				$obj->generateFilePath();
			}
		});
	}
}