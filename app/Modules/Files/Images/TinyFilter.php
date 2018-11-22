<?php

namespace App\Modules\Files\Images;

// 3rd Party
use Intervention\Image\Image;
use Intervention\Image\Filters\FilterInterface;

class TinyFilter implements FilterInterface
{
	public function applyFilter(Image $image)
	{
		return $image->fit(40, 40, function($constraint) {
			$constraint->upsize();
		});
	}
}