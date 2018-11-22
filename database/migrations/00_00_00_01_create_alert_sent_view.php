<?php

// Laravel
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateAlertSentView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW alert_sent AS
			SELECT u.id AS 'user_id', m.id AS 'membership_id', a.id AS 'alert_id', g.id AS 'gig_id'
			FROM alert a
			LEFT JOIN alert_gig ag ON a.id = ag.alert_id
			LEFT JOIN membership m ON a.membership_id = m.id
			LEFT JOIN user u ON m.user_id = u.id
			LEFT JOIN gig g ON ag.gig_id = g.id
			WHERE m.is_active = 1
			  AND u.is_active = 1
			  AND g.is_active = 1
			  AND g.deadline_at > NOW()
			  AND DATE_ADD(g.created_at, INTERVAL 14 DAY) > NOW()
			ORDER BY u.id ASC, a.id ASC
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW alert_sent");
	}
}
