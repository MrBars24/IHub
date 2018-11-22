<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkedAccountTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('linked_account', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned();
			$table->boolean('is_enabled')->default(true);
			$table->string('platform', 32);
			$table->string('name', 150);
			$table->string('native_id', 255);
			$table->string('token', 500)->nullable();
			$table->string('secret', 255)->nullable();
			$table->string('followers_label', 20)->nullable();
			$table->integer('followers')->unsigned()->nullable();
			$table->timestamp('linked_at');
			$table->timestamp('expired_at')->nullable();
			$table->string('expired_reason', 255)->nullable();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('linked_account');
	}
}
