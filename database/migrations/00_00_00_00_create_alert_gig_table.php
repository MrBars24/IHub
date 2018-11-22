<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertGigTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert_gig', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('alert_id')->unsigned()->nullable();
			$table->integer('gig_id')->unsigned()->nullable();
			$table->timestamp('viewed_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('alert_gig');
	}
}
