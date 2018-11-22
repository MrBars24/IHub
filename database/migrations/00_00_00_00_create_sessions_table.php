<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('sessions', function (Blueprint $table) {
			$table->string('id')->unique();
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('ip_address', 45)->nullable();
			$table->text('user_agent')->nullable();
			$table->text('payload');
			$table->integer('last_activity');
			$table->timestamps();

			// extra columns
			$table->string('device_token', 150)->nullable();
			$table->string('device_os', 20)->nullable(); // android, ios, blackberry, windows
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('sessions');
	}
}
