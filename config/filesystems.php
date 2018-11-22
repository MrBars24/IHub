<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Here you may specify the default filesystem disk that should be used
	| by the framework. The "local" disk, as well as a variety of cloud
	| based disks are available to your application. Just store away!
	|
	*/

	'default' => 'local.public',

	/*
	|--------------------------------------------------------------------------
	| Default Cloud Filesystem Disk
	|--------------------------------------------------------------------------
	|
	| Many applications store files both locally and in the cloud. For this
	| reason, you may specify a default "cloud" driver here. This driver
	| will be bound as the Cloud disk implementation in the container.
	|
	*/

	'cloud' => 's3',

	/*
	|--------------------------------------------------------------------------
	| Filesystem Disks
	|--------------------------------------------------------------------------
	|
	| Here you may configure as many filesystem "disks" as you wish, and you
	| may even configure multiple disks of the same driver. Defaults have
	| been setup for each driver as an example of the required options.
	|
	| Supported Drivers: "local", "ftp", "s3", "rackspace"
	|
	*/

	'disks' => [

		'local' => [
			'public' => [
				'driver' => 'local',
				'root'   => public_path('uploads'),
			],
			'temp' => [
				'driver' => 'local',
				'root'   => public_path('temp'),
			],
			'backups' => [
				'driver' => 'local',
				'root'   => storage_path('laravel-backups'),
			],
		],

		'public' => [
			'driver' => 'local',
			'root' => storage_path('app/public'),
			'visibility' => 'public',
		],

		's3' => [
			'driver' => 's3',
			'key' => 'your-key',
			'secret' => 'your-secret',
			'region' => 'your-region',
			'bucket' => 'your-bucket',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| Supported File Types
	|--------------------------------------------------------------------------
	|
	| Here you may configure the supported file types for the application.
	| The file types are sorted in groups for easy reference.
	|
	*/

	'filetypes' => [

		'image' => [
			'image/png' => '.png',
			'image/jpg' => '.jpg',
			'image/gif' => '.gif',
			'image/jpeg' => '.jpg',
		],

		'video' => [
			'video/mp4' => '.mp4',
			'video/avi' => '.avi',
			'video/mov' => '.mov',
			'video/quicktime' => '.mov',
			'video/ogg' => '.ogg',
		],

		'data' => [
			'text/csv' => '.csv',
		]
	]

];
