<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostAttachmentTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post_attachment', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('post_id')->unsigned();
			$table->string('title', 120)->nullalbe();
			$table->string('description', 500)->nullable();
			$table->string('source', 60)->nullable();
			$table->string('url', 255)->nullable();
			$table->string('shortened_url', 255)->nullable();
			$table->string('resource', 255)->nullable(); // the image or media asset for the top section
			$table->string('type', 10); // 'link', 'file', 'image', 'video', 'youtube', 'vimeo'
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('post_attachment');
	}
}
