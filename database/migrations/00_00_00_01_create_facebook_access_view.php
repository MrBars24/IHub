<?php

// Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateFacebookAccessView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW facebook_access AS
			SELECT a.id AS 'id', a.native_id AS 'native_id', a.native_id AS 'parent_id', a.name AS 'name', '' AS 'avatar', a.token AS 'access_token', 'profile' AS 'type', a.is_enabled AS 'is_active', 1 as 'display_order', a.id AS 'linked_id'
			FROM linked_account a
			WHERE platform = 'facebook'
			UNION ALL
			SELECT fb.id AS 'id', fb.profile_id AS 'native_id', fb.native_id AS 'parent_id', fb.name AS 'name', fb.avatar AS 'avatar', fb.access_token AS 'access_token', fb.type AS 'type', fb.is_active AS 'is_active', 2 as 'display_order', null AS 'linked_id'
			FROM facebook_page_access fb
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW facebook_access");
	}
}
