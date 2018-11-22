<?php

namespace App\Components;

interface EntityInterface
{
	/**
	 * Get the owners of this entity
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getOwners();
}