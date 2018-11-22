<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGigPostTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('gig_post', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('gig_id')->unsigned();
			$table->integer('post_id')->unsigned();
			$table->string('params', 8192)->nullable(); // for example, list of pinterest boards
			$table->string('status', 12); // 'draft', 'pending', 'scheduled', 'verified', 'rejected', 'superceded'
			$table->text('rejection_reason')->nullable();
			$table->string('schedule_result', 12); // 'pending', 'started', 'success', 'error'
			$table->string('schedule_error', 2048)->nullable();
			$table->timestamp('schedule_at')->nullable();
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
		Schema::dropIfExists('gig_post');
	}
}
