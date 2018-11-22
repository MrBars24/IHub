<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class AnimatedFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image; // just return unformatted
	}
}