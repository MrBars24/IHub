<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class LargeRatioFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->resize(800, null, function($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		})->encode('jpg', 85);
	}
}