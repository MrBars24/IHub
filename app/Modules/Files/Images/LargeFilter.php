<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class LargeFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->fit(240, 240, function($constraint) {
			$constraint->upsize();
		});
	}
}