<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ReportingTrait;

class SocialReporting extends Model
{
	use ReportingTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'linked_account';

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

	public function scopeSocialShares($query, $hub, $platforms, $startDate = null, $endDate = null)
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
		$joinBase = \DB::table('post_dispatch_queue AS s')
			->select(\DB::raw('
				IFNULL(COUNT(DISTINCT s.id), 0) AS `count`,
				FLOOR(DATEDIFF(s.created_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('s.hub_id', '=', $hub->id)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`'))
			->orderBy('intervals.interval');

		// join each platform
		foreach($platforms as $platform) {
			$join = clone $joinBase;
			$alias = 'shares_list_' . $platform;
			$query->addSelect(\DB::raw('IFNULL(' . $alias . '.`count`, 0) AS ' . $platform . '_count'))
				->leftJoin(\DB::raw('( ' . $join->where('platform', '=', $platform)->toSql() . ' ) AS ' . $alias), \DB::raw('intervals.`interval`'), '=', \DB::raw($alias . '.`interval`'))
				->mergeBindings($join);
		}

		return $query;
	}

	public function scopeSocialFollowers($query, $hub, $startDate = null, $endDate = null)
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

		// get control base
		$controlBase = \DB::table('linked_account AS d')
			->select(\DB::raw('
				m.id AS membership_id, AVG(d.followers) AS `followers`
			'))
			->leftJoin('membership AS m', 'd.user_id', '=', 'm.user_id')
			->where('m.hub_id', '=', $hub->id)
			->where('d.linked_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate)
			->where('d.linked_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
			->groupBy('m.id')
			->get();
		$controlBase = collect($controlBase);
		$sampleTotal = $controlBase->count();
		$sampleSegment = ceil($sampleTotal / 5); // count for top / bottom 20%
		$all = $controlBase->sortByDesc('followers')->pluck('membership_id')->toArray();
		$top = $controlBase->sortByDesc('followers')->take($sampleSegment)->pluck('membership_id')->toArray();
		$bottom = $controlBase->sortBy('followers')->take($sampleSegment)->pluck('membership_id')->toArray();

		// sub tables - define the data sets
		$join1a = \DB::table('linked_account')
			->select(\DB::raw('
				MAX(id) AS data_id,
				id as account_id,
				FLOOR(DATEDIFF(linked_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->groupBy(array('id', 'platform', 'interval'));

		$join1 = \DB::table('linked_account AS d')
			->select(\DB::raw('
				SUM(d.followers) AS `total_followers`,
				COUNT(DISTINCT m.id) AS `members_count`,
				SUM(d.followers) / COUNT(DISTINCT m.id) AS `average_followers`,
				`interval`
			'))
			->leftJoin(\DB::raw('( ' . $join1a->toSql() . ' ) AS p'), 'p.data_id', '=', 'd.id')
			->leftJoin('linked_account AS a', 'p.account_id', '=', 'a.id')
			->leftJoin('membership AS m', 'a.user_id', '=', 'm.user_id')
			->whereIn('m.id', $all)
			->where('m.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join2a = \DB::table('linked_account')
			->select(\DB::raw('
				MAX(id) AS data_id,
				id AS account_id,
				FLOOR(DATEDIFF(linked_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->groupBy(array('account_id', 'platform', 'interval'));

		$join2 = \DB::table('linked_account AS d')
			->select(\DB::raw('
				SUM(d.followers) AS `total_followers`,
				COUNT(DISTINCT m.id) AS `members_count`,
				SUM(d.followers) / COUNT(DISTINCT m.id) AS `average_followers`,
				`interval`
			'))
			->leftJoin(\DB::raw('( ' . $join2a->toSql() . ' ) AS p'), 'p.data_id', '=', 'd.id')
			->leftJoin('linked_account AS a', 'p.account_id', '=', 'a.id')
			->leftJoin('membership AS m', 'a.user_id', '=', 'm.user_id')
			->whereIn('m.id', $top)
			->where('m.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join3a = \DB::table('linked_account')
			->select(\DB::raw('
				MAX(id) AS data_id,
				id AS account_id,
				FLOOR(DATEDIFF(linked_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->groupBy(array('account_id', 'platform', 'interval'));

		$join3 = \DB::table('linked_account AS d')
			->select(\DB::raw('
				SUM(d.followers) AS `total_followers`,
				COUNT(DISTINCT m.id) AS `members_count`,
				SUM(d.followers) / COUNT(DISTINCT m.id) AS `average_followers`,
				`interval`
			'))
			->leftJoin(\DB::raw('( ' . $join3a->toSql() . ' ) AS p'), 'p.data_id', '=', 'd.id')
			->leftJoin('linked_account AS a', 'p.account_id', '=', 'a.id')
			->leftJoin('membership AS m', 'a.user_id', '=', 'm.user_id')
			->whereIn('m.id', $bottom)
			->where('m.hub_id', '=', $hub->id)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`,
		IFNULL(all_list.`total_followers`, 0) AS all_totals,
		IFNULL(top_list.`total_followers`, 0) AS top_totals,
		IFNULL(bottom_list.`total_followers`, 0) AS bottom_totals,
		IFNULL(all_list.`average_followers`, 0) AS all_averages,
		IFNULL(top_list.`average_followers`, 0) AS top_averages,
		IFNULL(bottom_list.`average_followers`, 0) AS bottom_averages'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS all_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('all_list.`interval`'))
			->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS top_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('top_list.`interval`'))
			->mergeBindings($join2)
			->leftJoin(\DB::raw('( ' . $join3->toSql() . ' ) AS bottom_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('bottom_list.`interval`'))
			->mergeBindings($join3)
			->orderBy('intervals.interval');

		return $query;
	}
}
