<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateErrorLogTable extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	 */
	public function up()
	{
		Schema::create('error_log', function (Blueprint $table) {
			$table->increments('id');
			$table->text('message');
			$table->string('exception_classname', 100);
			$table->integer('counter')->default(0);
			$table->boolean('is_sent')->default(false);
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
		 Schema::dropIfExists('error_log');
	}
}
