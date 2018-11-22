<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGigAttachmentTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gig_attachment', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('gig_id')->unsigned();
			$table->string('title', 120);
			$table->string('description', 500)->nullable();
			$table->string('source', 60);
			$table->string('url', 255)->nullable();
			$table->string('shortened_url', 255)->nullable();
			$table->string('resource', 255)->nullable();
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
		Schema::dropIfExists('gig_attachment');
	}
}
