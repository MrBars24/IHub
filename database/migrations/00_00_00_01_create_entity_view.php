<?php

// Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateEntityView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW entity AS
			SELECT user.id AS 'entity_id', user.slug AS 'entity_slug', user.name AS 'entity_name', 'App\\\\User' AS 'entity_type'
			FROM user
			UNION ALL
			SELECT hub.id AS 'entity_id', hub.slug AS 'entity_slug', hub.name AS 'entity_name', 'App\\\\Hub' AS 'entity_type'
			FROM hub
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW entity");
	}
}
