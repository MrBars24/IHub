<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationSettingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('notification_setting', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('membership_id')->unsigned();
			$table->integer('type_id')->unsigned();
			$table->boolean('send_web');
			$table->boolean('send_email');
			$table->boolean('send_push');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('notification_setting');
	}
}
