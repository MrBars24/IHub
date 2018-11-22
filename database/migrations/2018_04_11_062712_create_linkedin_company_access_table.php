<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinkedinCompanyAccessTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('linkedin_company_access', function(Blueprint $table) {
			$table->increments('id');
			$table->string('linked_id', 255)->nullable();
			$table->string('profile_id', 255);
			$table->string('native_id', 255);
			$table->boolean('is_active')->default(true);
			$table->string('name', 150);
			$table->string('type', 12);
			$table->string('avatar', 150)->nullable();
			$table->string('access_token', 500);
			$table->integer('follower_count')->unsigned()->nullable();
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
		Schema::dropIfExists('linkedin_company_access');
	}
}
