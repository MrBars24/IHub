<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class CoverOptimisedRatioFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		$large = is_phonegap() ? 1200 : 1600;
		return $image->resize($large, null, function($constraint) {
			$constraint->aspectRatio();
			$constraint->upsize();
		});
	}
}