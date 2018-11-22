<?php

// Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreatePinterestAccessView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW pinterest_access AS
			SELECT pi.id AS 'id', pi.profile_id AS 'native_id', pi.native_id AS 'parent_id', pi.name AS 'name', pi.avatar AS 'avatar', pi.access_token AS 'access_token', pi.type AS 'type', pi.is_active AS 'is_active', 1 as 'display_order', pi.linked_id AS 'linked_id'
			FROM pinterest_board_access pi
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW pinterest_access");
	}
}
