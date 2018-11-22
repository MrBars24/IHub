<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned();
			$table->integer('author_id')->unsigned();
			$table->string('author_type', 40); // 'App\User', 'App\Hub'
			$table->boolean('is_published')->default(false);
			$table->string('filesystem', 24)->nullable();
			$table->text('message')->nullable();
			$table->text('message_cached')->nullable(); // this will what will actually be displayed on the site
			$table->tinyInteger('failed_cache_attempts')->default(0);
			$table->text('failed_cache_reason')->nullable();
			$table->timestamp('cached_at')->nullable();
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
		Schema::dropIfExists('post');
	}
}
