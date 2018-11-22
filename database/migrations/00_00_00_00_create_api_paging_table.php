<?php

// Laravel
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiPagingTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('api_paging', function(Blueprint $table) {
			$table->increments('id');
			$table->string('token', 500)->nullable();
			$table->string('end_point_type', 50);
			$table->string('platform', 32);
			$table->string('next_page', 500)->nullable();
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
		Schema::dropIfExists('api_paging');
	}
}
