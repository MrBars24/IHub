<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccrualTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('point_accrual', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('membership_id')->unsigned();
			$table->integer('target_id')->unsigned()->nullable();
			$table->string('target_type', 40)->nullable();
			$table->string('action_type', 40)->nullable(); // postnew, postcomment, postshare, postlike
			$table->smallInteger('base_points')->unsigned();
			$table->smallInteger('points')->unsigned();
			$table->double('multiplier', 4, 1)->unsigned()->default(1.0);
			$table->timestamp('accrued_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('point_accrual');
	}
}
