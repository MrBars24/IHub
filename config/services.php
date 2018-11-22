<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, SparkPost and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/

	'mailgun' => [
		'domain' => env('MAILGUN_DOMAIN'),
		'secret' => env('MAILGUN_SECRET'),
	],

	'ses' => [
		'key' => env('SES_KEY'),
		'secret' => env('SES_SECRET'),
		'region' => 'us-east-1',
	],

	'sparkpost' => [
		'secret' => env('SPARKPOST_SECRET'),
	],

	'stripe' => [
		'model' => App\User::class,
		'key' => env('STRIPE_KEY'),
		'secret' => env('STRIPE_SECRET'),
	],

	// socialite: facebook
	'facebook' => [
		'client_id' => env('FACEBOOK_APP_ID', '1503378829975634'),
		'client_secret' => env('FACEBOOK_APP_SECRET', 'ce686c08b85c3d53ae6fdc75920e5135'),
		'redirect' => env('FACEBOOK_REDIRECT_URL'),
	],

	// socialite: twitter
	'twitter' => [
		'client_id' => env('TWITTER_APP_ID', 'GEPa1zPPBPu75co5fFk0Nj6x7'),
		'client_secret' => env('TWITTER_APP_SECRET', 'YNI9fuWHkweUzLyKNuEhqdFzZcenuWktFofYfH57gUM6DkMmyH'),
		'redirect' => env('TWITTER_REDIRECT_URL'),
	],

	// socialite: linkedin
	'linkedin' => [
		'client_id' => env('LINKEDIN_APP_ID', '753wdedrlfurd4'),
		'client_secret' => env('LINKEDIN_APP_SECRET', 'ZQcZf4HmF3XYEjq1'),
		'redirect' => env('LINKEDIN_REDIRECT_URL'),
	],

	// socialite: pinterest
	'pinterest' => [
		'client_id' => env('PINTEREST_APP_ID', '4819592665150533559'),
		'client_secret' => env('PINTEREST_APP_SECRET', '55e79b61e38661fd547d2edee2f31b4b1a7f36ae731f5980911f26975ff56862'),
		'redirect' => env('PINTEREST_REDIRECT_URL'),
	],

	// socialite: youtube
	'youtube' => [
		'client_id' => env('YOUTUBE_APP_ID', '183860302243-1ovd17nqufiibe9lorlkvdu3a9h6fldf.apps.googleusercontent.com'),
		'client_secret' => env('YOUTUBE_APP_SECRET', 'w28Y-H53jy55_jroyFtj7GVy'),
		'redirect' => env('YOUTUBE_REDIRECT_URL'),
	],

	// socialite: google
	'google' => [
		'client_id' => env('YOUTUBE_APP_ID', '183860302243-1ovd17nqufiibe9lorlkvdu3a9h6fldf.apps.googleusercontent.com'),
		'client_secret' => env('YOUTUBE_APP_SECRET', 'w28Y-H53jy55_jroyFtj7GVy'),
		'redirect' => env('YOUTUBE_REDIRECT_URL'),
	],

	// socialite: instagram
	'instagram' => [
		'client_id' => env('INSTAGRAM_APP_ID', '16a70f1e28484cfca4a6b0c1283095af'),
		'client_secret' => env('INSTAGRAM_APP_SECRET', '350e7a218cd94bc3adb081941bcf82bc'),
		'redirect' => env('INSTAGRAM_REDIRECT_URL'),
	],

	// phantomjs headless browser
	'phantomjs' => [
		'exe' => env('PHANTOM_JS_EXE', 'phantomjs'),
		'script' => env('PHANTOM_JS_SCRIPT', 'rasterise.js'),
	],

	// pushbots
	'pushbots' => [
		'app_id' => env('PUSHBOTS_APP_ID', '56e2c0de17795922308b4568'),
		'app_secret' => env('PUSHBOTS_APP_SECRET', 'd401f667825ba31a889f82040fb705fb')
	],
	
	// onesignal
	'onesignal' => [
		'app_id' => env('ONE_SIGNAL_APP_ID', 'ac8d5087-7ba8-49e5-a34c-7fb16b4837b2'),
		'rest_api_key' => env('ONE_SIGNAL_API_KEY', 'NDhlM2NjNjEtNDUxZi00NmZmLThjOWYtNGYxMTI4OTE5NGMx'),
		'user_auth_key' => env('ONE_SIGNAL_API_KEY', 'NDhlM2NjNjEtNDUxZi00NmZmLThjOWYtNGYxMTI4OTE5NGMx') // will be removed soon. user auth_key is the same as rest api key.
	]
];
