<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostReportTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// since similar database structure to post_hide, 
		// maybe we can merge it and just add an `action`field with values `report|hide`
		// and name it post_action table

		Schema::create('post_report', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('post_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->timestamps();

			$table->foreign('post_id')
				->references('id')->on('post')
				->onDelete('cascade');

			$table->foreign('user_id')
				->references('id')->on('user')
				->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('post_report', function (Blueprint $table) {
			$table->dropForeign(['user_id']);
			$table->dropForeign(['post_id']);
		});
		Schema::dropIfExists('post_report');
	}
}
