<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notification', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('type_id')->unsigned();
			$table->integer('receiver_id')->unsigned();
			$table->string('receiver_type', 40); // 'App\User', 'App\Hub'
			$table->integer('sender_id')->unsigned();
			$table->string('sender_type', 40); // 'App\User', 'App\Hub'
			$table->integer('behalf_id')->unsigned();
			$table->string('behalf_type', 40); // 'App\User', 'App\Hub'
			$table->string('link', 255);
			$table->string('profile', 32);
			$table->boolean('emitted_to_web')->default(false);
			$table->boolean('emitted_to_email')->default(false);
			$table->boolean('emitted_to_push')->default(false);
			$table->string('summary', 500);
			$table->text('message')->nullable();
			$table->text('message_cached')->nullable();
			$table->tinyInteger('failed_cache_attempts')->default(0);
			$table->text('failed_cache_reason')->nullable();
			$table->timestamp('cached_at')->nullable();
			$table->timestamp('read_at')->nullable();
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
		Schema::dropIfExists('notification');
	}
}
