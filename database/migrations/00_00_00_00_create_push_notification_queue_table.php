<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushNotificationQueueTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('push_notification_queue', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('notification_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('message', 255);
			$table->string('url', 255);
			$table->string('result', 10); // 'pending', 'started', 'success', 'error'
			$table->string('error', 2048)->nullable();
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
		Schema::dropIfExists('push_notification_queue');
	}
}
