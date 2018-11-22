<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLikeTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('like', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('content_id')->unsigned();
			$table->string('content_type', 40); // 'App\Post', 'App\Comment'
			$table->integer('liker_id')->unsigned();
			$table->string('liker_type', 40); // 'App\User', 'App\Hub'
			$table->boolean('is_liked');
			$table->timestamps();
			$table->timestamp('unliked_at')->nullable();
			$table->timestamp('reliked_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('like');
	}
}
