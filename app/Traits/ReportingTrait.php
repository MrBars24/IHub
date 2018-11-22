<?php

namespace App\Traits;

// 3rd Party
use Carbon\Carbon;

trait ReportingTrait
{
	/// Section: Properties

	protected static $DATES_GREATER_THAN_OPERATOR = '>=';
	protected static $DATES_LESSER_THAN_OPERATOR  = '<=';
	protected static $DATES_COMPARISON_FORMAT = 'Y-m-d H:i:s';
	protected static $DATES_INTERVAL_FORMAT   = 'Y-m-d';
	protected static $INTERVALS_HALF_COUNT = 6;
	protected static $INTERVALS_FULL_COUNT = 10;

	/// Section: Support

	public static function prepareIntervalsQuery($count, $size, $startDate)
	{
		// generate base - defines the intervals in the report
		$q = '';
		$from = $startDate->copy();
		$to = $startDate->copy()->addDays($size - 1);
		$start = -1;
		$from->subDays($size);
		$to->subDays($size);
		for($i = $start; $i < $count; $i++) {
			// interval, start_date, end_date
			$q .= ($i > $start ? 'UNION ALL ' : '') .
				'SELECT ' . $i . ($i == $start ? ' AS `interval`' : '') . ', ' .
				'\'' . $from->format('Y-m-d') . '\'' . ($i == $start ? ' AS `start_date`' : '') . ', ' .
				'\'' . $to->format('Y-m-d') . '\'' . ($i == $start ? ' AS `end_date`' : '') . ' ';
			$from->addDays($size);
			$to->addDays($size);
		}
		$q = '(' . $q . ') AS intervals';
		return $q;
	}

	public static function resolveDates($startDate = null, $endDate = null)
	{
		// default values
		if(is_null($startDate)) {
			//$startDate = Carbon::parse(Carbon::now()->format('Y-m-d 00:00:00'))->subMonth();
			$startDate = Carbon::parse(Carbon::now()->format('Y-m-d 00:00:00'))->subWeek();
		} else {
			$startDate = Carbon::parse(Carbon::parse($startDate)->format('Y-m-d 00:00:00'));
		}
		if(is_null($endDate)) {
			$endDate = Carbon::parse(Carbon::now()->format('Y-m-d 23:59:59'));
		} else {
			$endDate = Carbon::parse(Carbon::parse($endDate)->startOfDay()->subSecond()->format('Y-m-d 23:59:59'))->addDay();
		}

		return array($startDate, $endDate);
	}

	public static function getIntervals($count, $startDate = null, $endDate = null)
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// dates
		$d1 = new Carbon($startDate);
		$d2 = new Carbon($endDate);

		// range checking
		if($d1 > $d2) {
			$d1 = clone $d2;
		}

		// get range
		$range = $d1->diffInDays($d2) + 1;

		// get size
		$size = max(intval(floor($range / $count)), 1);

		// get final interval count. It might be different than what was specified
		$count = 0;
		while($d1 < $d2) {
			$d1->addDays($size);
			$count++;
		}
		if($size * $count < $range) {
			$count++;
		}

		// return info
		$info = array(
			'count' => $count,
			'size' => $size,
			'range' => $range,
			'startDate' => $startDate,
			'endDate' => $endDate
		);
		return $info;
	}
}