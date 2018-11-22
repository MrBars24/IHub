<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInstagramConnectionTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('instagram_connection', function(Blueprint $table) {
			$table->increments('id');
			$table->string('profile_id', 255);
			$table->string('native_id', 255);
			$table->boolean('is_active')->default(true);
			$table->string('screen_name', 40);
			$table->string('display_name', 150);
			$table->string('type', 20);
			$table->string('avatar', 150)->nullable();
			$table->string('access_token', 500);
			$table->string('end_point_type', 40);
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
		Schema::dropIfExists('instagram_connection');
	}
}
