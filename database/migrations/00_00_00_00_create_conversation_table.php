<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConversationTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('conversation', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('receiver_id')->unsigned();
			$table->string('receiver_type', 40); // 'App\User', 'App\Hub'
			$table->integer('sender_id')->unsigned();
			$table->string('sender_type', 40); // 'App\User', 'App\Hub'
			$table->integer('last_message_id')->unsigned();
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
		Schema::dropIfExists('conversation');
	}
}
