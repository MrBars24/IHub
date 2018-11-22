<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSharingMetaLinkedinFieldOnHubTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('hub', function (Blueprint $table) {
			$table->string('sharing_meta_linkedin', 50)->nullable()->after('default_gig_require_approval');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('hub', function (Blueprint $table) {
			$table->dropColumn('sharing_meta_linkedin');
		});
	}
}
