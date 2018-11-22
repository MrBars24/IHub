<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertCycleTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_cycle', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned()->nullable();
			$table->integer('job_id')->unsigned()->nullable();
			$table->string('name', 60); // hub#123/2015-07-26-23-50
			$table->mediumInteger('alert_count')->unsigned();
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
		Schema::dropIfExists('alert_cycle');
	}
}
