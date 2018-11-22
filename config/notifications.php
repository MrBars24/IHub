<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Notifications
	|--------------------------------------------------------------------------
	|
	| Configuration for notifications.
	|
	*/

	'site' => [
		'name' => env('NOTIFICATIONS_SITE_NAME')
	],

	'profiles' => [

		'notification' => [
			'template'  => 'email.notification',
			'subject'   => env('NOTIFICATIONS_EMAIL_SUBJECT', 'New {site.name} Notification'),
			'from_name' => env('NOTIFICATIONS_EMAIL_FROM_NAME', '{sender.name} via {site.name}'),
			'class'     => env('NOTIFICATIONS_PHP_CLASS'),
		],

		'direct_message' => [
			'template'  => 'email.message',
			'subject'   => env('MESSAGES_EMAIL_SUBJECT', 'New Private Message on {site.name}'),
			'from_name' => env('MESSAGES_EMAIL_FROM_NAME', '{sender.name} via {site.name}'),
			'class'     => env('MESSAGES_PHP_CLASS'),
		],
	]

];
