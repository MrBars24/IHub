<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostDispatchJobTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post_dispatch_job', function(Blueprint $table) {
			$table->increments('id');
			$table->string('result', 10); // 'started', 'success', 'error'
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
		Schema::dropIfExists('post_dispatch_job');
	}
}
