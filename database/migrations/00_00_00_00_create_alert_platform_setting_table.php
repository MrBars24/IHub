<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertPlatformSettingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_platform_setting', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('membership_id')->unsigned();
			$table->integer('platform_id')->unsigned();
			$table->boolean('is_selected')->default(false);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('alert_platform_setting');
	}
}
