<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointResetTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('point_reset', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('membership_id')->unsigned();
			$table->string('action_type', 40)->nullable(); // manualreset, leaderboardreset
			$table->mediumInteger('points_before_reset');
			$table->timestamp('reset_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('point_reset');
	}
}
