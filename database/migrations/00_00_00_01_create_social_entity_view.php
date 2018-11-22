<?php

// Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSocialEntityView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW social_entity AS
			SELECT a.id AS 'entity_id', 'App\\\\LinkedAccount' AS 'entity_type', a.native_id AS 'native_id', a.native_id AS 'parent_id', a.name AS 'name', '' AS 'avatar', a.token AS 'token', a.secret AS 'secret', a.is_enabled AS 'is_active', a.id AS 'linked_id', a.platform AS 'platform'
			FROM linked_account a
			UNION ALL
			SELECT fb.id AS 'entity_id', 'App\\\\FacebookPageAccess' AS 'entity_type', fb.profile_id AS 'native_id', fb.native_id AS 'parent_id', fb.name AS 'name', fb.avatar AS 'avatar', fb.access_token AS 'token', null AS 'secret', fb.is_active AS 'is_active', null AS 'linked_id', 'facebook' AS 'platform'
			FROM facebook_page_access fb
			UNION ALL
			SELECT pi.id AS 'entity_id', 'App\\\\PinterestBoardAccess' AS 'entity_type', pi.profile_id AS 'native_id', pi.native_id AS 'parent_id', pi.name AS 'name', pi.avatar AS 'avatar', pi.access_token AS 'token', null AS 'secret', pi.is_active AS 'is_active', pi.linked_id AS 'linked_id', 'pinterest' AS 'platform'
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
		DB::statement("DROP VIEW social_entity");
	}
}
