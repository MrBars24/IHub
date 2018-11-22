<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class CoverRatioFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->resize(1680, null, function($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		})->encode('jpg', 85);
	}
}