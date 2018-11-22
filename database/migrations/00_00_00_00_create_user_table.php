<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name', 120);
			$table->string('slug', 120);
			$table->string('summary', 1024)->nullable();
			$table->string('email', 190)->unique();
			$table->string('password', 85);
			$table->string('password_raw', 40)->nullable();
			$table->string('profile_picture', 40)->nullable();
			$table->string('cover_picture', 40)->nullable();
			$table->string('profile_picture_cropping', 150)->nullable(); // 150 length will suffice the json length i think
			$table->string('cover_picture_cropping', 150)->nullable();
			$table->string('profile_picture_display', 15)->default('square');
			$table->boolean('is_master')->default(false);
			$table->boolean('is_active')->default(false);
			$table->boolean('is_taggable')->default(true);
			$table->boolean('receive_push_notifications')->default(true);
			$table->string('filesystem', 24)->nullable();
			$table->rememberToken();
			$table->timestamp('last_login_at')->nullable();
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
		Schema::dropIfExists('user');
	}
}
