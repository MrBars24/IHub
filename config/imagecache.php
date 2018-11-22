<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Name of route
	|--------------------------------------------------------------------------
	|
	| Enter the routes name to enable dynamic imagecache manipulation.
	| This handle will define the first part of the URI:
	| 
	| {route}/{template}/{filename}
	| 
	| Examples: "images", "img/cache"
	|
	*/
   
	'route' => 'images',

	/*
	|--------------------------------------------------------------------------
	| Storage paths
	|--------------------------------------------------------------------------
	|
	| The following paths will be searched for the image filename, submited 
	| by URI. 
	| 
	| Define as many directories as you like.
	|
	*/
	
	'paths' => array(
		public_path('upload'),
		public_path('images'),
		public_path('temp')
	),

	/*
	|--------------------------------------------------------------------------
	| Manipulation templates
	|--------------------------------------------------------------------------
	|
	| Here you may specify your own manipulation filter templates.
	| The keys of this array will define which templates 
	| are available in the URI:
	|
	| {route}/{template}/{filename}
	|
	| The values of this array will define which filter class
	| will be applied, by its fully qualified name.
	|
	*/
   
	'templates' => array(

		// user
		'tiny'   => App\Modules\Files\Images\TinyFilter::class,    // 40 x 40,    1:1 ratio
		'small'  => App\Modules\Files\Images\SmallFilter::class,   // 80 x 80,    1:1 ratio
		'medium' => App\Modules\Files\Images\MediumFilter::class,  // 180 x 180,  1:1 ratio

		// post_attachment
		'large_ratio' => App\Modules\Files\Images\LargeRatioFilter::class, // 800 x ?

		// gig_attachment
		'medium_ratio' => App\Modules\Files\Images\MediumRatioFilter::class, // 240 x ?

		// safe image
		'safe_image' => App\Modules\Files\Images\NewsFeedFilter::class, // 600 x ?

	),

	/*
	|--------------------------------------------------------------------------
	| Image Cache Lifetime
	|--------------------------------------------------------------------------
	|
	| Lifetime in minutes of the images handled by the imagecache route.
	|
	*/
   
	'lifetime' => 43200,

);
