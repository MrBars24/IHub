<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ReportingTrait;

class AlertReporting extends Model
{
	use ReportingTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'alert';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['created_at'];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = [];

	/// Section: Reporting

	public function scopeAlertInteractions($query, $hub, $startDate = null, $endDate = null)
	{
		// intervals
		$intervals = self::getIntervals(static::$INTERVALS_FULL_COUNT, $startDate, $endDate);
		$startDate = $intervals['startDate'];
		$endDate   = $intervals['endDate'];
		$size  = $intervals['size'];
		$count = $intervals['count'];

		// generate base - defines the intervals in the report
		$q = self::prepareIntervalsQuery($count, $size, $startDate);
		$query->from(\DB::raw($q));

		// sub tables - define the data sets
		$join1 = \DB::table('alert AS a')
			->select(\DB::raw('
				COUNT(DISTINCT a.id) AS `alert_count`,
				FLOOR(DATEDIFF(a.sent_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join2 = \DB::table('alert AS a')
			->select(\DB::raw('
				COUNT(DISTINCT a.id) AS `alert_count`,
				FLOOR(DATEDIFF(a.read_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join3a = \DB::table('alert_gig AS g')
			->select(\DB::raw('
				alert_id,
				MIN(viewed_at) AS viewed_at
			'))
			->whereNotNull('viewed_at')
			->groupBy('alert_id');
		$join3 = \DB::table('alert AS a')
			->select(\DB::raw('
				COUNT(DISTINCT a.id) AS `alert_count`,
				FLOOR(DATEDIFF(g.viewed_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->join(\DB::raw('( ' . $join3a->toSql() . ' ) AS g'), 'a.id', '=', 'g.alert_id')
			->where('a.hub_id', '=', $hub->id)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`,
		IFNULL(sent_list.`alert_count`, 0) AS sent_totals,
		IFNULL(opened_list.`alert_count`, 0) AS opened_totals,
		IFNULL(clicked_list.`alert_count`, 0) AS clicked_totals'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS sent_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('sent_list.`interval`'))
				->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS opened_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('opened_list.`interval`'))
				->mergeBindings($join2)
			->leftJoin(\DB::raw('( ' . $join3->toSql() . ' ) AS clicked_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('clicked_list.`interval`'))
				->mergeBindings($join3)
			->orderBy('intervals.interval');

		return $query;
	}

	public function scopeClickThroughRates($query, $hub, $startDate = null, $endDate = null)
	{
		// intervals
		$intervals = self::getIntervals(static::$INTERVALS_HALF_COUNT, $startDate, $endDate);
		$startDate = $intervals['startDate'];
		$endDate   = $intervals['endDate'];
		$size  = $intervals['size'];
		$count = $intervals['count'];

		// generate base - defines the intervals in the report
		$q = self::prepareIntervalsQuery($count, $size, $startDate);
		$query->from(\DB::raw($q));

		// sub tables - define the data sets
		$join1a = \DB::table('alert AS a')
			->select(\DB::raw('
				a.membership_id,
				COUNT(distinct g2.id) AS gig_count,
				FLOOR(DATEDIFF(a.sent_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->leftJoin('alert_gig AS g2', 'a.id', '=', \DB::raw('g2.alert_id AND g2.viewed_at IS NOT NULL'))
			->where('a.hub_id', '=', $hub->id)
			->groupBy('a.membership_id');
		$join1 = \DB::table('alert AS a')
			->select(\DB::raw('
				COUNT(DISTINCT membership_id) AS `member_count`,
				SUM(gig_count) AS `click_count`,
				`interval`
			'))
			->from(\DB::raw('( ' . $join1a->toSql() . ' ) AS g'))
			->mergeBindings($join1a)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`,
		IFNULL(member_click_list.`member_count`, 0) AS member_totals,
		IFNULL(member_click_list.`click_count`, 0) AS click_totals'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS member_click_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('member_click_list.`interval`'))
			->mergeBindings($join1)
			->orderBy('intervals.interval');

		return $query;
	}

	public function scopeCategoryPreferences($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// structure
		$query->select(\DB::raw('s.category_id, category.name, COUNT(s.membership_id) AS count'))
			->from('alert_category_setting AS s')
			->join('gig_category AS c', 's.category_id', '=', 'c.id')
			->join('category', 's.category_id', '=', 'category.id')
			->groupBy('s.category_id')
			->orderBy('count', 'DESC');

		// hub
		$query->where('category.hub_id', '=', $hub->id);

		// start date
		if(!is_null($startDate)) {
			//$query->where('c.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
		}

		// end date
		if(!is_null($endDate)) {
			$query->where('category.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate);
		}

		return $query;
	}
}
