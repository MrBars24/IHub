<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ReportingTrait;

class InfluencerReporting extends Model
{
	use ReportingTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user';

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
	protected $appends = ['performance_score_sort', 'influence_score_sort'];

	/// Section: Mutators

	public function getRatingAttribute()
	{
		if(!isset($this->attributes['rating']) || is_null($this->attributes['rating'])) {
			return 0.0;
		}
		return number_format($this->attributes['rating'], 1);
	}

	public function getPerformanceScoreAttribute()
	{
		$score = $this->points + ($this->rating * 100);
		return $score;
	}

	public function getInfluenceScoreAttribute()
	{
		$followerImpact = 10000;
		$followerMultiplier = 1;
		$multiplier = 3 * 3 * 3 * 3;
		while($this->followers > ($multiplier *= 3)) {
			$followerMultiplier++;
		}
		$score = ($followerImpact + $this->performance_score) * $followerMultiplier;
		return $score;
	}

	public function getPerformanceScoreSortAttribute()
	{
		$sort = $this->id + $this->performance_score * 100000;
		return $sort;
	}

	public function getInfluenceScoreSortAttribute()
	{
		$sort = $this->id + $this->influence_score * 100000;
		return $sort;
	}

	// Section : Reporting

	public function scopeInfluencerPoints($query, $hub, $startDate = null, $endDate = null)
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
		$controlBase = \DB::table('point_accrual AS a')
			->select(\DB::raw('
				a.membership_id, SUM(a.points) AS `points`
			'))
			->leftJoin('membership AS m', 'm.id', '=', 'a.membership_id')
			->where('m.hub_id', '=', $hub->id)
			->where('a.accrued_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate)
			->where('a.accrued_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
			->groupBy('a.membership_id')
			->get();
		$controlBase = collect($controlBase);
		$sampleTotal = $controlBase->count();
		$sampleSegment = ceil($sampleTotal / 5); // count for top / bottom 20%
		$all = $controlBase->sortByDesc('avg_rating')->pluck('membership_id')->toArray();
		$top = $controlBase->sortByDesc('points')->take($sampleSegment)->pluck('membership_id')->toArray();
		$bottom = $controlBase->sortBy('points')->take($sampleSegment)->pluck('membership_id')->toArray();

		// sub tables - define the data sets
		$join1 = \DB::table('point_accruals_daily AS a')
			->select(\DB::raw('
				SUM(total_points) AS `total_points`,
				COUNT(DISTINCT membership_id) AS `members_count`,
				SUM(total_points) / COUNT(DISTINCT membership_id) AS `average_points`,
				FLOOR(DATEDIFF(a.accrued_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->whereIn('a.membership_id', $all)
			->groupBy('interval');

		$join2 = \DB::table('point_accruals_daily AS a')
			->select(\DB::raw('
				SUM(total_points) AS `total_points`,
				COUNT(DISTINCT membership_id) AS `members_count`,
				SUM(total_points) / COUNT(DISTINCT membership_id) AS `average_points`,
				FLOOR(DATEDIFF(a.accrued_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->whereIn('a.membership_id', $top)
			->groupBy('interval');

		$join3 = \DB::table('point_accruals_daily AS a')
			->select(\DB::raw('
				SUM(total_points) AS `total_points`,
				COUNT(DISTINCT membership_id) AS `members_count`,
				SUM(total_points) / COUNT(DISTINCT membership_id) AS `average_points`,
				FLOOR(DATEDIFF(a.accrued_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->whereIn('a.membership_id', $bottom)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`,
		IFNULL(all_list.`total_points`, 0) AS all_totals,
		IFNULL(top_list.`total_points`, 0) AS top_totals,
		IFNULL(bottom_list.`total_points`, 0) AS bottom_totals,
		IFNULL(all_list.`average_points`, 0) AS all_averages,
		IFNULL(top_list.`average_points`, 0) AS top_averages,
		IFNULL(bottom_list.`average_points`, 0) AS bottom_averages'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS all_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('all_list.`interval`'))
				->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS top_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('top_list.`interval`'))
				->mergeBindings($join2)
			->leftJoin(\DB::raw('( ' . $join3->toSql() . ' ) AS bottom_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('bottom_list.`interval`'))
				->mergeBindings($join3)
			->orderBy('intervals.interval');

		return $query;
	}

	public function scopeAchievementsEarned($query, $hub, $startDate = null, $endDate = null)
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
		$join1 = \DB::table('achievements_almost_daily AS a')
			->select(\DB::raw('
				SUM(total_count) AS `total_count`,
				FLOOR(DATEDIFF(a.almost_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('a.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join2 = \DB::table('achievements_reached_daily AS r')
			->select(\DB::raw('
				SUM(total_count) AS `total_count`,
				FLOOR(DATEDIFF(r.reached_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('r.hub_id', '=', $hub->id)
			->groupBy('interval');

		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`,
		IFNULL(almost_list.`total_count`, 0) AS almost_count,
		IFNULL(reached_list.`total_count`, 0) AS reached_count'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS almost_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('almost_list.`interval`'))
				->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS reached_list'), \DB::raw('intervals.`interval`'), '=', \DB::raw('reached_list.`interval`'))
				->mergeBindings($join2)
			->orderBy('intervals.interval');

		return $query;
	}

	public function scopeInfluencerPerformanceNumbers($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// points can be retrieved
		// 1) to date, or
		// 2) accrued over a time range **currently used**

		// sub tables - define the data sets
		//$lastResetAt = 'NULL';
		$lastResetAt = '(SELECT MAX(reset_at) FROM `point_reset` WHERE reset_at ' . static::$DATES_GREATER_THAN_OPERATOR . ' \'' . $startDate->format(static::$DATES_COMPARISON_FORMAT) . '\' AND membership_id = pa.membership_id)';
		$accruals = \DB::table('point_accruals_daily AS pa')
			->select(\DB::raw('
				pa.membership_id, SUM(total_points) AS points, accrued_at AS activity_at,
				' . $lastResetAt . ' AS last_reset_at
			'))
			->groupBy(array('pa.membership_id', 'activity_at'));
		/*$transactions = \DB::table('point_reset AS pr')
			->select(\DB::raw('
				pr.membership_id, SUM(points_before_reset * -1) AS points, reset_at AS activity_at
			'))
			->groupBy(array('pr.membership_id', 'activity_at'))
			->union($accruals);*/
		$transactions = $accruals;

		$join1 = \DB::table('membership AS m')
			->select(\DB::raw('
				m.hub_id, m.id AS membership_id, SUM(tally.points) AS points, tally.activity_at
			'))
			->leftJoin(\DB::raw('( ' . $transactions->toSql() . ' ) AS tally'), 'm.id', '=', 'tally.membership_id')
			->where('m.hub_id', '=', $hub->id)
			->where('m.role', '=', 'influencer')
			//->where('tally.activity_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate) // we need to get point accruals and reset from the beginning
			->where(function($query) use ($startDate) {
				$query->whereRaw('tally.activity_at >= last_reset_at')
					->orWhereNull('last_reset_at')
					->where('tally.activity_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
			})
			->where('tally.activity_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
			->groupBy('m.id');

		$join2 = \DB::table('membership AS m')
			->select(\DB::raw('
				m.hub_id, m.id AS membership_id, SUM(a.followers) AS followers
			'))
			->leftJoin('linked_account AS a', 'm.user_id', '=', 'a.user_id')
			->where('m.hub_id', '=', $hub->id)
			->where('m.role', '=', 'influencer')
			->groupBy('m.id');

		// $  = \DB::table('membership AS m')
		// 	->select(\DB::raw('
		// 		m.hub_id, m.id AS membership_id, AVG(e.rating) AS rating, fulfilled_at
		// 	'))
		// 	->leftJoin('engagement AS e', 'm.user_id', '=', 'e.user_id')
		// 	->whereNotNull('rating')
		// 	->where('m.hub_id', '=', $hub->id)
		// 	->where('m.role', '=', 'influencer')
		// 	->where('e.fulfilled_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate)
		// 	->where('e.fulfilled_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
		// 	->groupBy('m.id');

		// structure
		$query->select(\DB::raw('membership.id AS membership_id, user.id, user.name,
		IFNULL(points_list.`points`, 0) AS points,
		IFNULL(followers_list.`followers`, 0) AS followers'))
			->from('user')
			->leftJoin('membership', 'membership.user_id', '=', \DB::raw('user.id AND membership.hub_id = ' . $hub->id))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS points_list'), \DB::raw('membership.`id`'), '=', \DB::raw('points_list.`membership_id`'))
				->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS followers_list'), \DB::raw('membership.`id`'), '=', \DB::raw('followers_list.`membership_id`'))
				->mergeBindings($join2)
			// ->leftJoin(\DB::raw('( ' . $join3->toSql() . ' ) AS ratings_list'), \DB::raw('membership.`id`'), '=', \DB::raw('ratings_list.`membership_id`'))
			// 	->mergeBindings($join3)
			->where('role', '=', 'influencer');

		return $query;
	}
}
