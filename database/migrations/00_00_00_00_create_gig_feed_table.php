<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGigFeedTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gig_feed', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->string('type', 16); // 'rssfeed', 'facebookpage', 'twitteraccount', 'instagramaccount', 'pinterestboard', 'googlepluspage', linkedincompany
			$table->string('source_url', 255); // store for URL's for above fields
			$table->text('options')->nullable(); // json object that holds other field options if applicable
			$table->string('evaluated_url', 255); // store the generated RSS bridge URL here
			$table->boolean('is_active')->default(false);
			$table->string('last_result', 7)->default('pending'); // 'pending', 'started', 'success', 'error'
			$table->string('last_error', 8192)->nullable();
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
		Schema::dropIfExists('gig_feed');
	}
}
