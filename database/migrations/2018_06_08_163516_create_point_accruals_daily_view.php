<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccrualsDailyView extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("
		CREATE OR REPLACE VIEW point_accruals_daily AS
			SELECT hub_id, membership_id, SUM(a.points) AS total_points, YEAR(accrued_at) AS yr, MONTH(accrued_at) AS mth, DAY(accrued_at) AS dy, MIN(accrued_at) AS accrued_at
			FROM point_accrual a
			LEFT JOIN membership m ON a.membership_id = m.id
			GROUP BY membership_id, yr, mth, dy
			ORDER BY yr ASC, mth ASC, dy ASC;
		");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("DROP VIEW point_accruals_daily");
	}
}
