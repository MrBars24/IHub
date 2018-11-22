<?php

namespace App;

use App\Traits\ReportingTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\RuntimeException;

class GigReporting extends Model
{
	use ReportingTrait;

	// table
	protected $table = "gig";

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['days_gap_label', 'performance_score_sort'];

	// section : mutator

	public function getPerformanceScoreAttribute()
	{
		// dependent attributes:
		// completed_count
		// points
		if(!isset($this->attributes['completed_count']) || !isset($this->attributes['points'])) {
			return null;
		}

		$score = $this->completed_count * ($this->points + 1000);
		return $score;
	}

	public function getPerformanceScoreSortAttribute()
	{
		$sort = $this->id + $this->performance_score * 100000;
		return $sort;
	}

	public function getDaysGapLabelAttribute()
	{
		// dependent attributes:
		// days_gap
		if(!isset($this->attributes['days_gap'])) {
			return null;
		}

		$days_gap = $this->attributes['days_gap'];

		$overdue = '';
		// on time
		if($days_gap == 0) {
			return 'On time';
		}
		// early
		elseif($days_gap > 0) {
			$overdue = ' early';
		}
		// overdue
		elseif($days_gap < 0) {
			$days_gap *= -1;
			$overdue = ' overdue';
		}
		return $days_gap . ' day' . ($days_gap != 1 ? 's' : '') . $overdue;
	}

	// section : scopes

	public function scopeGigNumbers($query, $hub, $startDate = null, $endDate = null)
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
		$join1 = \DB::table('gig')
			->select(\DB::raw('
				COUNT(*) AS `count`,
				FLOOR(DATEDIFF(gig.commence_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->where('gig.hub_id', '=', $hub->id)
			->groupBy('interval');

		$join2 = \DB::table('gig_post')
			->select(\DB::raw('
				COUNT(*) AS `count`,
				FLOOR(DATEDIFF(gig_post.created_at, \'' . $startDate->format(static::$DATES_INTERVAL_FORMAT) . '\') / ' . $size . ') AS `interval`
			'))
			->leftJoin('gig', 'gig_post.gig_id', 'gig.id')
			->where('gig.hub_id', '=', $hub->id)
			->where(function ($query) {
				$query->where('status', '=', 'verified')
					  ->orWhere('status', '=', 'scheduled');
			})
			->groupBy('interval');


		// structure
		$query->select(\DB::raw('intervals.`interval`, intervals.`start_date`, intervals.`end_date`, IFNULL(commenced.`count`, 0) AS commenced_count, IFNULL(fulfilled.`count`, 0) AS fulfilled_count'))
			->leftJoin(\DB::raw('( ' . $join1->toSql() . ' ) AS commenced'), \DB::raw('intervals.`interval`'), '=', \DB::raw('commenced.`interval`'))
				->mergeBindings($join1)
			->leftJoin(\DB::raw('( ' . $join2->toSql() . ' ) AS fulfilled'), \DB::raw('intervals.`interval`'), '=', \DB::raw('fulfilled.`interval`'))
				->mergeBindings($join2)
			->orderBy('intervals.interval');

		return $query;
	}

	public function scopeParticipationPlatforms($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// structure
		$query->select(\DB::raw('pd.platform, COUNT(pd.platform) AS count'))
			->from('gig_post AS gp')
			->join('post_dispatch_queue AS pd', 'gp.post_id', '=', 'pd.post_id')
			->groupBy('pd.platform')
			->orderBy('count', 'DESC');

		// hub
		$query->where('pd.hub_id', '=', $hub->id);

		// start date
		if(!is_null($startDate)) {
			$query->where('gp.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
		}

		// end date
		if(!is_null($endDate)) {
			$query->where('gp.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate);
		}

		return $query;
	}

	public function scopeParticipationCategories($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// structure
		$query->select(\DB::raw('c.name, COUNT(gc.category_id) AS count'))
			->from('gig_post AS gp')
			->leftJoin('gig_category AS gc', 'gp.gig_id', '=', 'gc.gig_id')
			->join('category AS c', 'gc.category_id', '=', 'c.id')
			->groupBy('gc.category_id')
			->orderBy('count', 'DESC');

		// hub
		$query->where('c.hub_id', '=', $hub->id);
		$query->whereNotNull('gc.category_id');

		// start date
		if(!is_null($startDate)) {
			$query->where('gp.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
		}

		// end date
		if(!is_null($endDate)) {
			$query->where('gp.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate);
		}

		return $query;
	}

	public function scopePunctualityFirstPost($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// generate base - defines the intervals in the report
		$gig_puctuality = \DB::table("gig AS g")
			->select(\DB::raw("g.id, hub_id, deadline_at, MIN(gp.created_at) as created_at"))
			->join("gig_post AS gp", "g.id", "=", "gp.gig_id")
			->groupBy("g.id");
		
		$query->from(\DB::raw('( SELECT hub_id, created_at, DATEDIFF(deadline_at, created_at) AS days_gap FROM ( '. $gig_puctuality->toSql() .' ) as b ) AS a'))
			->mergeBindings($gig_puctuality);

		// hub
		$query->where('a.hub_id', '=', $hub->id);

		// start date
		if(!is_null($startDate)) {
			$query->where('a.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
		}

		// end date
		if(!is_null($endDate)) {
			$query->where('a.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate);
		}

		// structure
		$query->select(\DB::raw('days_gap, COUNT(days_gap) AS count'))
			->groupBy('days_gap')
			->orderBy('days_gap', 'DESC');	

		return $query;
	}

	public function scopePunctualityCompletion($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		$gig_verified = \DB::table('gig AS g')
			->select(\DB::raw('g.id, hub_id, deadline_at, g.created_at'))
			->join('gig_post AS gp', 'g.id', '=', 'gp.gig_id')
			->where('gp.status', '=', "verified");

		// generate base - defines the intervals in the report
		$query->from(\DB::raw('(SELECT hub_id, created_at, DATEDIFF(deadline_at, created_at) AS days_gap FROM (' . \DB::raw('( ' . $gig_verified->toSql()) . ') as b ) ) AS a'))
			->mergeBindings($gig_verified);

		// hub
		$query->where('a.hub_id', '=', $hub->id);

		// start date
		if(!is_null($startDate)) {
			$query->where('a.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate);
		}

		// end date
		if(!is_null($endDate)) {
			$query->where('a.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate);
		}

		// structure
		$query->select(\DB::raw('days_gap, COUNT(days_gap) AS count'))
			->groupBy('days_gap')
			->orderBy('days_gap', 'DESC');

		return $query;
	}

	public function scopeGigPerformanceNumbers($query, $hub, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// structure
		$query->select(\DB::raw('g.id, g.hub_id, g.title, g.points, g.created_at, SUM(if(gp.status = "verified" OR gp.status = "scheduled", 1, 0)) AS completed_count'))
			->from('gig AS g')
			->leftJoin('gig_post AS gp', 'g.id', '=', 'gp.gig_id')
			->where('g.hub_id', '=', $hub->id)
			->where(function($query) use ($startDate, $endDate) {
				$query->where('gp.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate)
					->where('gp.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
					->orWhereNull('gp.created_at');
			})
			->groupBy('g.id')
			->having('g.created_at', static::$DATES_GREATER_THAN_OPERATOR, $startDate)
			->having('g.created_at', static::$DATES_LESSER_THAN_OPERATOR, $endDate)
			->orHaving('completed_count', '>', 0);

		return $query;
	}
}
