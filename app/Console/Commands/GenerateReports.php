<?php

namespace App\Console\Commands;

// App
use App\Hub;
use App\ReportSnapshot;

// Laravel
use Illuminate\Console\Command;

// 3rd Party
use Carbon\Carbon;

class GenerateReports extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'reports:generate';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate reports for each hub';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// set flags for run frequencies
		$monthly = $this->isStartOfMonth();

		// get hubs
		$hubs = Hub::query()
			->where('is_active', '=', true)
			->get();

		// get screens
		$screens = array(
			'gigs',
			'influencers',
			'alerts',
		);

		// get dates
		// daily: start of previous day till just before start of current day         [typically run every day midnight]
		// weekly: start of previous sunday till just before start of current sunday  [typically run on Sunday midnight]
		// monthly: 1st of previous month till just before start of current month     [typically run on 1st day of month midnight]
		foreach($hubs as $i => $hub) {
			foreach($screens as $j => $screen) {
				// run monthly
				//if($monthly['run'] === true) {
					// create snapshot
					with(new ReportSnapshot)->generateScheduledReport($screen, $hub, $monthly['start_date'], $monthly['end_date']);
				//}
			}
		}
	}

	protected function isStartOfMonth()
	{
		$currDate = Carbon::now();

		// run
		$day  = $currDate->format('j');
		$hour = $currDate->format('G');
		$run = $day == '1' && $hour < '9'; // runnable in the first 9 hours of each month to allow for debugging

		// date range
		$startDate = $currDate->copy()->subMonth()->startOfMonth();
		$endDate   = $currDate->copy()->subMonth()->endOfMonth();

		return array(
			'run' => $run,
			'start_date' => $startDate->format('Y-m-d'),
			'end_date' => $endDate->format('Y-m-d'),
		);
	}
}
