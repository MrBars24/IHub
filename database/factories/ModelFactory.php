<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */

$factory->define(App\AlertCategorySetting::class, function (Faker\Generator $faker) {
	return [
		'is_selected' => $faker->boolean,
	];
});

$factory->define(App\AlertPlatformSetting::class, function (Faker\Generator $faker) {
	return [
		'is_selected' => $faker->boolean,
	];
});

$factory->define(App\Category::class, function (Faker\Generator $faker) {
	return [
		'name' => $faker->name,
		'is_active' => true,
	];
});

$factory->define(App\Comment::class, function (Faker\Generator $faker) {
	return [
		'message' => $faker->paragraph,
		'is_published' => true,
	];
});

$factory->define(App\Conversation::class, function (Faker\Generator $faker) {
	return [
		'receiver_type' => 'App\User',
		'sender_type' => 'App\User',
	];
});

$factory->define(App\Gig::class, function (Faker\Generator $faker) {
	$now = carbon();
	$commence_at = carbon()->startOfDay()->addDays($faker->numberBetween(-22, 10))->addHours($faker->numberBetween(7, 17))->addMinutes($faker->numberBetween(0, 3) * 15);
	$deadline_at = $commence_at->copy()->startOfDay()->addDays($faker->numberBetween(5, 15))->addHours($faker->numberBetween(7, 17))->addMinutes($faker->numberBetween(0, 3) * 15);
	$has_commenced_notified = $commence_at < carbon() ? $faker->boolean(75) : false;
	$has_expiring_notified = $deadline_at < carbon()->addHours(8) ? $faker->boolean(75) : false;
	$has_expired_notified = $deadline_at < carbon() ? $faker->boolean(75) : false;
	return [
		'title' => $faker->words(5, true),
		'is_active' => true,
		'is_live' => ($commence_at <= $now && $deadline_at > $now),
		'description' => $faker->paragraph,
		'ideas' => $faker->paragraph,
		'place_count' => $faker->numberBetween(3, 8),
		'points' => $faker->numberBetween(3, 8) * 10,
		'commence_at' => $commence_at,
		'deadline_at' => $deadline_at,
		'has_commenced_notified' => $has_commenced_notified,
		'has_expiring_notified' => $has_expiring_notified,
		'has_expired_notified' => $has_expired_notified,
	];
});

$factory->define(App\GigAttachment::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->company,
		'description' => $faker->paragraph,
		'source' => $faker->domainName,
	];
});

$factory->define(App\Hub::class, function (Faker\Generator $faker) {
	return [
		'summary' => $faker->paragraph,
		'is_active' => true,
	];
});

$factory->define(App\Like::class, function (Faker\Generator $faker) {
	$is_liked = $faker->boolean(80);
	$unliked_at = null;
	$reliked_at = null;
	if($is_liked && $faker->boolean(20)) {
		$reliked_at = carbon()->subMinute($faker->numberBetween(35, 830));
		$clone = clone $reliked_at;
		$unliked_at = $clone->subMinute($faker->numberBetween(3, 70));
	} elseif(!$is_liked) {
		$unliked_at = carbon()->subMinute($faker->numberBetween(35, 830));
	}
	return [
		'is_liked' => $is_liked,
		'unliked_at' => $unliked_at,
		'reliked_at' => $reliked_at,
	];
});

$factory->define(App\LinkedAccount::class, function (Faker\Generator $faker) {
	return [
	];
});

$factory->define(App\Membership::class, function (Faker\Generator $faker) {
	return [
		'status' => 'member',
		'points' => 0,
		'is_active' => true,
	];
});

$factory->define(App\Message::class, function (Faker\Generator $faker) {
	return [
		'message' => $faker->realText,
	];
});

$factory->define(App\Post::class, function (Faker\Generator $faker) {
	return [
		'message' => $faker->paragraph,
		'is_published' => true,
	];
});

$factory->define(App\PostAttachment::class, function (Faker\Generator $faker) {
	return [
		'title' => $faker->company,
		'description' => $faker->paragraph,
		'source' => $faker->domainName,
	];
});

$factory->define(App\Reward::class, function (Faker\Generator $faker) {
	return [
		'description' => $faker->paragraph,
	];
});

$factory->define(App\User::class, function (Faker\Generator $faker) {
	static $password;
	return [
		'name' => $faker->name,
		'email' => $faker->unique()->safeEmail,
		'summary' => $faker->paragraph,
		'is_active' => true,
		'is_master' => false,
		'password' => $password ?: $password = 'secret', // no need to call bcrypt here; the User model handles the call there
		'remember_token' => str_random(10),
	];
});

// placeholders

$factory->define(App\NotificationSetting::class, function (Faker\Generator $faker) {
	return [
	];
});

$factory->define(App\NotificationType::class, function (Faker\Generator $faker) {
	return [
	];
});

$factory->define(App\Platform::class, function (Faker\Generator $faker) {
	return [
	];
});