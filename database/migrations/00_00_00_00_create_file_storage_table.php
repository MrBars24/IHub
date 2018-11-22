<?php

// Laravel
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileStorageTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('file_storage', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('object_id')->unsigned()->nullable();
			$table->string('object_type', 40)->nullable(); // various model classes
			$table->string('context', 40)->nullable();
			$table->string('path', 255);
			$table->string('status', 10); // 'staged', 'live', 'superceded', 'missing'
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
		Schema::dropIfExists('file_storage');
	}
}
