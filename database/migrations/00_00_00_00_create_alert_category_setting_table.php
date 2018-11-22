<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertCategorySettingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_category_setting', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('membership_id')->unsigned();
			$table->integer('category_id')->unsigned();
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
		Schema::dropIfExists('alert_category_setting');
	}
}
