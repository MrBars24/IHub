<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostDispatchQueueTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post_dispatch_queue', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('job_id')->unsigned()->nullable();
			$table->integer('post_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('native_id', 255)->nullable();
			$table->string('context', 40); // postwrite, postshare, gigfulfill
			$table->string('platform', 32); // facebook, etc...
			$table->string('params', 8000)->nullable(); // json object of extra data. for example, list of pinterest boards
			$table->string('result', 10)->default('pending'); // 'not ready', 'pending', 'started', 'success', 'error', 'failed'
			$table->string('error', 2048)->nullable();
			$table->text('message');
			$table->tinyInteger('attempts')->unsigned()->default(0);
			$table->boolean('hanging_notified')->default(false);
			$table->tinyInteger('certainty')->unsigned()->nullable();
			$table->timestamps();
			$table->timestamp('started_at')->nullable();
			$table->timestamp('finished_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('post_dispatch_queue');
	}
}
