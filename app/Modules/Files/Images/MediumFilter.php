<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class MediumFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->fit(180, 180, function($constraint) {
			$constraint->upsize();
		});
	}
}