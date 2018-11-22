<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessageTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('message', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('conversation_id')->unsigned();
			$table->integer('sender_id')->unsigned();
			$table->string('sender_type', 40); // 'App\User', 'App\Hub'
			$table->text('message')->nullable();
			$table->text('message_cached')->nullable();
			$table->tinyInteger('failed_cache_attempts')->default(0);
			$table->text('failed_cache_reason')->nullable();
			$table->timestamp('cached_at')->nullable();
			$table->timestamp('read_at')->nullable();
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
		Schema::dropIfExists('message');
	}
}
