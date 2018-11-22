<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Traits\MutatorsTrait;
use App\Traits\TimestampableTrait;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Carbon\Carbon;

class ReportSnapshot extends Model
{
	// App
	use MutatorsTrait, TimestampableTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'report_snapshots';

	/**
	 * The attributes that should be visible in arrays.
	 *
	 * @var array
	 */
	protected $visible = ['id', 'download_url', 'export_file', 'hub_id', 'result', 'run_type', 'screen', 'created_at'];

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
	protected $appends = ['run_type_display', 'screen_display'];

	/// Section: TimestampableTrait

	public function getTimestampAttributes()
	{
		return array(
			'created_at',
		);
	}

	/// Section: Mutators

	public function getRunTypeDisplayAttribute()
	{
		switch($this->run_type) {
			case 'ondemand':
				return 'On demand';
				break;
			case 'scheduled':
				return 'Scheduled';
				break;
		}
		return '-';
	}

	public function getScreenDisplayAttribute()
	{
		return ucfirst($this->screen);
	}

	/// Section: Methods

	public static function resolveDates($startDate = null, $endDate = null)
	{
		// default values
		if(is_null($startDate)) {
			$startDate = Carbon::parse(Carbon::now()->format('Y-m-d 00:00:00'))->subMonth();
		} else {
			$startDate = Carbon::parse(Carbon::parse($startDate)->format('Y-m-d 00:00:00'));
		}
		if(is_null($endDate)) {
			$endDate = Carbon::parse(Carbon::now()->format('Y-m-d 00:00:00'))->addDay();
		} else {
			$endDate = Carbon::parse(Carbon::parse($endDate)->subSecond()->format('Y-m-d 00:00:00'))->addDay();
		}

		return array($startDate, $endDate);
	}

	public function generateOnDemandReport($screen, $hub, $startDate, $endDate, $params = array())
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// current user
		$user = auth()->user()->id;

		// populate model object
		$this->hub_id = $hub->id;
		$this->screen = $screen;
		$this->result = 'pending';
		$this->run_type = 'ondemand';
		$this->created_by = $user;
		$this->save();

		// prepare params
		$params = array(
			'hub' => $hub->slug,
			'screen' => $screen,
			'start_date' => $startDate->format('Y-m-d'),
			'end_date' => $endDate->format('Y-m-d'),
			'snapshot' => $this->id,
			'hs' => 'b35493ed70c0dd3137d5782eae0a2f38'
		) + $params;
		$this->params = json_encode($params);
		$this->save();

		$time = time();

		// get route and file info
		$address = route('hub::report.preview', $params);
		$filename = 'report-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '-' . $screen . '-' . $time . '.pdf';

		// store
		$basePath = public_path('uploads');
		$directory = 'reports/' . $hub->id . '/';
		
		if(!Storage::exists($directory)) {
			$umask = umask(0);
			File::makeDirectory($basePath . '/' . $directory, 0755, true);
			umask($umask);
		}
		$path = $basePath . '/' . $directory . $filename;

		// prepare command
		$esc = env('CLI_ESCAPE_CHAR', '^');
		$address = str_replace('&', $esc . '&', $address);
		$binPath = app_path('Support' . DIRECTORY_SEPARATOR . 'bin');
		$command = implode(' ', array(
			'"' . $binPath . DIRECTORY_SEPARATOR . config('services.phantomjs.exe') . '"',
			'"' . $binPath . DIRECTORY_SEPARATOR . config('services.phantomjs.script') . '"',
			$address,
			$path,
			//'A4'
		));
		
		// run command
		$process = new Process($command, __DIR__);
		$process->setTimeout(30);
		$process->run();

		// output
		$result = 'success';
		if($errorOutput = $process->getErrorOutput()) {
			try {
				throw new RuntimeException('PhantomJS: ' . $errorOutput);
			} catch(RuntimeException $e) {
				$result = 'error';
			}
		}

		// update model object
		$this->export_file = $filename;
		$this->download_url = app('url')->to('/uploads/reports/' . $hub->id . '/' . $filename);
		$this->result = $result;
		$this->error = $errorOutput;
		$this->command = $command;
		$this->save();

		return $this;
	}

	public function generateScheduledReport($screen, $hub, $startDate, $endDate, $params = array())
	{
		// default dates
		$dates = self::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// current user
		$user = null;

		// populate model object
		$this->hub_id = $hub->id;
		$this->screen = $screen;
		$this->result = 'pending';
		$this->run_type = 'scheduled';
		$this->created_by = $user;
		$this->save();

		// prepare params
		$params = array(
				'hub' => $hub->slug,
				'screen' => $screen,
				'start_date' => $startDate->format('Y-m-d'),
				'end_date' => $endDate->format('Y-m-d'),
				'snapshot' => $this->id,
				'hs' => 'b35493ed70c0dd3137d5782eae0a2f38'
			) + $params;
		$this->params = json_encode($params);
		$this->save();

		$time = time();

		// get route and file info
		$address = route('hub::report.preview', $params);
		$filename = 'report-' . $startDate->format('Ymd') . '-' . $endDate->format('Ymd') . '-' . $screen . '-' . $time . '.pdf';

		// store
		$basePath = public_path('uploads');
		$directory = 'reports/' . $hub->id . '/';
		if(!Storage::exists($directory)) {
			$umask = umask(0);
			File::makeDirectory($basePath . '/' . $directory, 0755, true);
			umask($umask);
		}
		$path = $basePath . '/' . $directory . $filename;

		// prepare command
		$esc = env('CLI_ESCAPE_CHAR');
		$address = str_replace('&', $esc . '&', $address);
		$binPath = app_path('Support' . DIRECTORY_SEPARATOR . 'bin');
		$command = implode(' ', array(
			'"' . $binPath . DIRECTORY_SEPARATOR . config('services.phantomjs.exe') . '"',
			'"' . $binPath . DIRECTORY_SEPARATOR . config('services.phantomjs.script') . '"',
			$address,
			$path,
			//'A4'
		));

		// run command
		$process = new Process($command, __DIR__);
		$process->setTimeout(30);
		$process->run();

		// output
		$result = 'success';
		if($errorOutput = $process->getErrorOutput()) {
			try {
				throw new RuntimeException('PhantomJS: ' . $errorOutput);
			} catch(RuntimeException $e) {
				$result = 'error';
			}
		}

		// update model object
		$this->export_file = $filename;
		$this->download_url = app('url')->to('/uploads/reports/' . $hub->id . '/' . $filename);
		$this->result = $result;
		$this->error = $errorOutput;
		$this->command = $command;
		$this->save();

		return $this;
	}
}
