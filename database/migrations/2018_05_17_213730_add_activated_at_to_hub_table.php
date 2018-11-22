<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Hub;
use Illuminate\Support\Facades\DB;

class AddActivatedAtToHubTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('hub', function (Blueprint $table) {
			$table->timestamp('activated_at')->nullable();
			$table->timestamp('deactivated_at')->nullable();
		});

		// update all hub
		foreach (Hub::all() as $hub) {
			DB::update('update hub set activated_at = ? where id = ?', [$hub->created_at, $hub->id]);
			DB::update('update hub set deactivated_at = ? where id = ? AND is_active = 0', [$hub->updated_at, $hub->id]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('hub', function (Blueprint $table) {
			$table->dropColumn('activated_at');
			$table->dropColumn('deactivated_at');
		});
	}
}
