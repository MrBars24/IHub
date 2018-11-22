<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAlertTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('alert', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('cycle_id')->unsigned()->nullable();
			$table->integer('membership_id')->unsigned()->nullable();
			$table->string('cycle_name', 60); // hub#123/2015-07-26-23-50
			$table->string('email', 255);
			$table->mediumInteger('gig_count')->unsigned();
			$table->timestamp('sent_at')->nullable();
			$table->timestamp('read_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('alert');
	}
}
