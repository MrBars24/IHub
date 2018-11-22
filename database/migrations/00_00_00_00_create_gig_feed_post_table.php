<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGigFeedPostTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gig_feed_post', function (Blueprint $table) {
			$table->increments('id');
			$table->string('native_id', 40)->nullable();
			$table->integer('hub_id')->unsigned();
			$table->string('type', 16); // 'rssfeed', 'facebookpage', 'twitteraccount', 'instagramaccount', 'pinterestboard', 'googlepluspage'
			$table->string('title', 255); // should be rename to author
			$table->string('description', 4000)->nullable();
			$table->string('thumbnail', 255)->nullable();
			$table->string('link', 255);
			$table->string('url_profile', 255)->nullable(); // should we include the url profile as well?
			$table->timestamp('originally_published_at');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('gig_feed_post');
	}
}
