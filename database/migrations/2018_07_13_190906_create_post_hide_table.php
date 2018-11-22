<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostHideTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post_hide', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('post_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->boolean('is_hidden', false);
			$table->timestamps();
			$table->timestamp('unhid_at')->nullable();
			$table->timestamp('rehid_at')->nullable();

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
		Schema::table('post_hide', function (Blueprint $table) {
			$table->dropForeign(['user_id']);
			$table->dropForeign(['post_id']);
		});
		Schema::dropIfExists('post_hide');
	}
}
