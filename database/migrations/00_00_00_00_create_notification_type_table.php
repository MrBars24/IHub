<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationTypeTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notification_type', function(Blueprint $table) {
			$table->increments('id');
			$table->string('key', 40);
			$table->string('label', 60);
			$table->string('profile', 32);
			$table->string('enabled_for', 32); // 'all', 'influencer', 'hubmanager'
			$table->boolean('is_enabled')->default(true);
			$table->boolean('send_web')->default(false);
			$table->boolean('send_email')->default(false);
			$table->boolean('send_push')->default(false);
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
		Schema::dropIfExists('notification_type');
	}
}
