<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScrapedUrlTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('scraped_url', function(Blueprint $table) {
			$table->increments('id');
			$table->string('requested_url', 255);
			$table->string('title', 255)->nullable();
			$table->string('description', 1024)->nullable();
			$table->string('url', 255)->nullable();
			$table->string('type', 12)->nullable(); // 'link', 'photo', 'video', 'rich'
			$table->string('image', 255)->nullable();
			$table->mediumInteger('image_width')->nullable();
			$table->mediumInteger('image_height')->nullable();
			$table->text('embed_code')->nullable();
			$table->mediumInteger('embed_width')->nullable();
			$table->mediumInteger('embed_height')->nullable();
			$table->double('aspect_ratio', 10, 8)->nullable();
			$table->string('author_name', 120)->nullable();
			$table->string('author_url', 255)->nullable();
			$table->string('provider_name', 120)->nullable();
			$table->string('provider_url', 255)->nullable();
			$table->string('provider_icon', 120)->nullable();
			$table->timestamp('published_at')->nullable();
			$table->string('license', 120)->nullable();
			$table->smallInteger('response_code')->nullable();
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
		Schema::dropIfExists('scraped_url');
	}
}
