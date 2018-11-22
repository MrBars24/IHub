<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcceptedConditionsToMembershipTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('membership', function (Blueprint $table) {
			$table->boolean('accepted_conditions')->default(0);
			$table->timestamp('accepted_conditions_at')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('membership', function (Blueprint $table) {
			$table->dropColumn('accepted_conditions');
			$table->dropColumn('accepted_conditions_at');
		});
	}
}
