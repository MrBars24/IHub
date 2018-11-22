<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportSnapshotsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('report_snapshots', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('hub_id')->unsigned()->nullable();
			$table->string('screen', 15); // gigs, users, alerts, socialmedia
			$table->string('params', 500)->nullable();
			$table->string('export_file', 60);
			$table->string('download_url', 250);
			$table->enum('result', array('pending', 'success', 'error'));
			$table->string('error', 2048)->nullable();
			$table->string('command', 500)->nullable();
			$table->string('run_type', 15); // scheduled, ondemand
			$table->integer('created_by')->unsigned()->nullable();
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
		Schema::dropIfExists('report_snapshots');
	}
}
