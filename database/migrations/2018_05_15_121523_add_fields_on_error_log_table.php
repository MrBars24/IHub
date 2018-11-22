<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsOnErrorLogTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('error_log', function (Blueprint $table) {
			$table->string('environment', 10);
			$table->string('route_name', 100)->nullable();
			$table->string('route_action', 255)->nullable();
			$table->string('request_uri', 500);
			$table->string('request_method', 10);
			$table->string('response_code', 3);
			$table->text('request_input');
			$table->string('auth_user', 255)->nullable();
			$table->timestamp('sent_at')->nullable();;
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	Schema::table('error_log', function (Blueprint $table) {
	$table->dropColumn('environment');
			$table->dropColumn('route_name');
			$table->dropColumn('route_action');
			$table->dropColumn('request_uri');
			$table->dropColumn('request_method');
			$table->dropColumn('response_code');
			$table->dropColumn('request_input');
			$table->dropColumn('auth_user');
			$table->dropColumn('sent_at');
	});
	}
}
