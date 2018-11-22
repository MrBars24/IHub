<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class SmallFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->fit(80, 80, function($constraint) {
			$constraint->upsize();
		});
	}
}