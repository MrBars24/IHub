<?php

namespace App\Http\Controllers\Hub;

use Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Hub;
use App\GigReporting;
use App\InfluencerReporting;
use App\AlertReporting;
use App\Platform;
use App\SocialReporting;
use App\ReportSnapshot;

class ReportingController extends Controller
{
	/**
	 * GET /api/{hub}/newsfeed
	 * ROUTE hub::post.feed [api.php]
	 * 
	 * Get report based on screen
	 *
	 * @param Request $request
	 * @param Hub $hub
	 * @param String $screen
	 * @return \Illuminate\Http\Response
	 */
	public function getReport(Request $request, Hub $hub, $screen)
	{
		// params
		$startDate = $request->input('start_date');
		$endDate   = $request->input('end_date');
		$dates = GigReporting::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// reports based on screen
		switch($screen) {
			case 'gigs':
				$view_file = 'report.gig';
				$data = $this->getGigsData($hub, $startDate, $endDate);
				$data['title'] = "Gigs Report";
				break;
			case 'influencers':
				$view_file = 'report.influencer';
				$data = $this->getInfluencersData($hub, $startDate, $endDate);
				$data['title'] = "Influencers Report";
				break;
			case 'alerts':
				$view_file = 'report.alert';
				$data = $this->getAlertsData($hub, $startDate, $endDate);
				$data['title'] = "Alerts Report";
				break;
			case 'social':
				$view_file = 'report.social';
				$data = $this->getSocialData($hub, $startDate, $endDate);
				$data['title'] = "Social Report";
				break;
		}

		// push start and end date
		$data += array(
			'start_date' => $startDate->format('Y-m-d'),
			'end_date'   => $endDate->format('Y-m-d'),
		);

		// check if route is from preview
		// throw response
		$name = Route::currentRouteName();
		if($name == 'hub::reporting.gig') {
			return response()->json($data);
		}else {
			return view($view_file, $data);
		}
		
	}

	/**
	 * Report snapshot histories
	 *
	 * @param Request $request
	 * @param Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function history(Request $request, Hub $hub)
	{
		// params
		$startDate = $request->input('start_date');
		$endDate   = $request->input('end_date');
		$dates = GigReporting::resolveDates($startDate, $endDate);
		$startDate = $dates[0];
		$endDate   = $dates[1];

		// get backlink
		$params = $request->all();
		$params = array_merge([$hub->slug], $params);

		// init query
		$snapshots = ReportSnapshot::query();

		// set defaults if not exist
		if(!isset($params['start_date'])) {
			$params['start_date'] = $startDate->format('Y-m-d');
		}

		if(!isset($params['end_date'])) {
			$params['end_date'] = $endDate->format('Y-m-d');
		}

		if(isset($params['screen'])) {
			$snapshots->where('screen', '=', $params['screen']);
		}

		$backLink = route('hub::reporting.gig', $params);

		// get snapshot
		$result = $snapshots->where('hub_id', '=', $hub->id)
			->where(function ($query) use($params) {
				$query->where('created_at', '>=', $params['start_date'])
						->where('created_at', '<=', date('Y-m-d', strtotime($params['end_date'] . "+1 days")));
			})
			->where('result', '=', 'success')
			->orderBy('created_at', 'DESC')
			->get();

		// response
		$data = array(
			'snapshots' => $result,
			'backLink' => $backLink
		);
		
		// throw response
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/reporting/preview/{screen}
	 * ROUTE hub::reporting.preview [api.php]
	 * 
	 * export base on hub and screen
	 *
	 * @param Request $request
	 * @param Hub $hub
	 * @param String $screen
	 * @return \Illuminate\Http\Response
	 */
	public function export(Request $request, Hub $hub, $screen = 'gigs')
	{
		$startDate = $request->input('start_date');
		$endDate   = $request->input('end_date');

		$snapshot = with(new ReportSnapshot)->generateOnDemandReport($screen, $hub, $startDate, $endDate);

		// response
		$data = array(
			'snapshot' => $snapshot
		);

		setcookie('fileDownload', 'true', 0, '/');
		return response()->json($data);
	}

	// helpers

	/**
	 * Get gigs data for report
	 *
	 * @param Hub $hub
	 * @param mixed $startDate
	 * @param mixed $endDate
	 * @return Array
	 */
	public function getGigsData(Hub $hub, $startDate, $endDate)
	{
		// chart options
		$lineChartOptions     = $this->getLineChartOptions();
		$doughnutChartOptions = $this->getDoughnutChartOptions();

		// 2) Gig Numbers [line]
		$gigNumbers = GigReporting::gigNumbers($hub, $startDate, $endDate)->get();
		
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'Commenced',
				'data' => $gigNumbers->pluck('commenced_count'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Fulfilled',
				'data' => $gigNumbers->pluck('fulfilled_count'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
		);

		$chart_gig_numbers = array(
			'type' => 'line',
			'data' => array(
				'labels' => $gigNumbers->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 2) participation_platforms [doughnut]
		$gigParticipationPlatforms = GigReporting::participationPlatforms($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'data' => $gigParticipationPlatforms->pluck('count'),
			), $doughnutChartOptions['datasetStyles']),
		);
		$chart_gig_participation_platforms = array(
			'type' => 'doughnut',
			'data' => array(
				'labels' => $gigParticipationPlatforms->pluck('platform'),
				'datasets' => $datasets
			),
			'options' => $doughnutChartOptions['options']
		);

		// 3) participation_categories [doughnut]
		$gigParticipationCategories = GigReporting::participationCategories($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'data' => $gigParticipationCategories->pluck('count'),
			), $doughnutChartOptions['datasetStyles']),
		);
		$chart_gig_participation_categories = array(
			'type' => 'doughnut',
			'data' => array(
				'labels' => $gigParticipationCategories->pluck('name'),
				'datasets' => $datasets
			),
			'options' => $doughnutChartOptions['options']
		);
		
		// 4) punctuality_first_post [line]
		$gigPunctualityFirstPost = GigReporting::punctualityFirstPost($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'Days early / behind (first post)',
				'data' => $gigPunctualityFirstPost->pluck('count'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_gig_punctuality_first_post = array(
			'type' => 'line',
			'data' => array(
				'labels' => $gigPunctualityFirstPost->pluck('days_gap_label'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 5) punctuality_completion [line]
		$gigPunctualityCompletion = GigReporting::punctualityCompletion($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'Days early / behind (completion)',
				'data' => $gigPunctualityCompletion->pluck('count'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_gig_punctuality_completion = array(
			'type' => 'line',
			'data' => array(
				'labels' => $gigPunctualityCompletion->pluck('days_gap_label'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 6) high_gigs [list]
		$gigs = GigReporting::gigPerformanceNumbers($hub, $startDate, $endDate)->get();

		$list_high_gigs = $gigs->sortByDesc('performance_score_sort')->take(6);
		$list_high_gigs = array(
			'title' => 'Top Performing Gigs',
			'columns' => array(
				array('title',           'pure-u-1-2', 'Name'),
				array('completed_count', 'pure-u-1-4', 'Completed'),
				array('points',          'pure-u-1-4', 'Points')
			),
			'data' => $list_high_gigs->values()->toArray()
		);

		// 7) low_gigs [list]
		$list_low_gigs = $gigs->sortBy('performance_score_sort')->take(6);
		$list_low_gigs = array(
			'title' => 'Bottom Performing Gigs',
			'columns' => array(
				array('title',           'pure-u-1-2', 'Name'),
				array('completed_count', 'pure-u-1-4', 'Completed'),
				array('points',          'pure-u-1-4', 'Points')
			),
			'data' => $list_low_gigs->values()->toArray()
		);

		// return
		return array(
			'chart_gig_numbers' => $chart_gig_numbers,
			'chart_gig_participation_platforms' => $chart_gig_participation_platforms,
			'chart_gig_participation_categories' => $chart_gig_participation_categories,
			'chart_gig_punctuality_first_post' => $chart_gig_punctuality_first_post,
			'chart_gig_punctuality_completion' => $chart_gig_punctuality_completion,
			'list_high_gigs' => $list_high_gigs,
			'list_low_gigs' => $list_low_gigs,
		);
	}

	/**
	 * Charts for influencers
	 *   1) total_points [line]
	 *   2) average_points [line]
	 *   3) high_performers [list]
	 *   4) low_performers [list]
	 *   5) high_influencers [list]
	 *   6) low_influencers [list]
	 *
	 * @param $hub
	 * @param $startDate
	 * @param $endDate
	 * @return array
	 */
	protected function getInfluencersData($hub, $startDate, $endDate)
	{
		// chart options
		$lineChartOptions = $this->getLineChartOptions();
		$barChartOptions  = $this->getBarChartOptions();

		// 1) average_points [line]
		$influencerPoints = InfluencerReporting::influencerPoints($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'All',
				'data' => $influencerPoints->pluck('all_totals'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Top 20%',
				'data' => $influencerPoints->pluck('top_totals'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Bottom 20%',
				'data' => $influencerPoints->pluck('bottom_totals'),
			), $lineChartOptions['datasetStyles'][2], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_influencer_total_points = array(
			'type' => 'line',
			'data' => array(
				'labels' => $influencerPoints->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 2) total_points [line]
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'All',
				'data' => $influencerPoints->pluck('all_averages'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Top 20%',
				'data' => $influencerPoints->pluck('top_averages'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Bottom 20%',
				'data' => $influencerPoints->pluck('bottom_averages'),
			), $lineChartOptions['datasetStyles'][2], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_influencer_average_points = array(
			'type' => 'line',
			'data' => array(
				'labels' => $influencerPoints->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 3) high_performers [list]
		$influencers = InfluencerReporting::influencerPerformanceNumbers($hub, $startDate, $endDate)->get();

		$list_high_performers = $influencers->sortByDesc('performance_score_sort')->take(6);
		$list_high_performers = array(
			'title' => 'Top Performers',
			'columns' => array(
				array('name',   'pure-u-1-2', 'Name'),
				array('points', 'pure-u-1-4', 'Points')
			),
			'data' => $list_high_performers->values()->toArray()
		);

		// 4) low_performers [list]
		$list_low_performers = $influencers->sortBy('performance_score_sort')->take(6);
		$list_low_performers = array(
			'title' => 'Bottom Performers',
			'columns' => array(
				array('name',   'pure-u-1-2', 'Name'),
				array('points', 'pure-u-1-4', 'Points')
			),
			'data' => $list_low_performers->values()->toArray()
		);

		// 7) high_influencers [list]
		$list_high_influencers = $influencers->sortByDesc('influence_score_sort')->take(6);
		$list_high_influencers = array(
			'title' => 'Top Influencers',
			'columns' => array(
				array('name',      'pure-u-1-2', 'Name'),
				array('points',    'pure-u-1-4', 'Points'),
				array('followers', 'pure-u-1-4', 'Followers'),
			),
			'data' => $list_high_influencers->values()->toArray()
		);

		// 8) low_influencers [list]
		$list_low_influencers = $influencers->sortBy('influence_score_sort')->take(6);
		$list_low_influencers = array(
			'title' => 'Bottom Influencers',
			'columns' => array(
				array('name',      'pure-u-1-2', 'Name'),
				array('points',    'pure-u-1-4', 'Points'),
				array('followers', 'pure-u-1-4', 'Followers'),
			),
			'data' => $list_low_influencers->values()->toArray()
		);

		// return
		return array(
			'chart_influencer_average_points' => $chart_influencer_average_points,
			'chart_influencer_total_points' => $chart_influencer_total_points,
			'list_high_performers' => $list_high_performers,
			'list_low_performers' => $list_low_performers,
			'list_high_influencers' => $list_high_influencers,
			'list_low_influencers' => $list_low_influencers,
		);
	}

	/**
	 * Charts for alerts
	 *   1) alert_interactions [line]
	 *   2) alert_clickthrough_rates [line]
	 *   3) category_preferences [doughnut]
	 *
	 * @param $hub
	 * @param $startDate
	 * @param $endDate
	 * @return array
	 */
	protected function getAlertsData($hub, $startDate, $endDate)
	{
		// chart options
		$lineChartOptions     = $this->getLineChartOptions();
		$doughnutChartOptions = $this->getDoughnutChartOptions();

		// 1) alert_interactions [line]
		$alertInteractions = AlertReporting::alertInteractions($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'Sent',
				'data' => $alertInteractions->pluck('sent_totals'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Opened',
				'data' => $alertInteractions->pluck('opened_totals'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Clicked through',
				'data' => $alertInteractions->pluck('clicked_totals'),
			), $lineChartOptions['datasetStyles'][2], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_alert_interactions = array(
			'type' => 'line',
			'data' => array(
				'labels' => $alertInteractions->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 2) total_points [line]
		$alertClickThroughs = AlertReporting::clickThroughRates($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'Members',
				'data' => $alertClickThroughs->pluck('member_totals'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Click Throughs',
				'data' => $alertClickThroughs->pluck('click_totals'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_alert_clickthrough_rates = array(
			'type' => 'line',
			'data' => array(
				'labels' => $alertClickThroughs->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 3) category_preferences [doughnut]
		$categoryPreferences = AlertReporting::categoryPreferences($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'data' => $categoryPreferences->pluck('count'),
			), $doughnutChartOptions['datasetStyles']),
		);
		$chart_alert_category_preferences = array(
			'type' => 'doughnut',
			'data' => array(
				'labels' => $categoryPreferences->pluck('name'),
				'datasets' => $datasets
			),
			'options' => $doughnutChartOptions['options']
		);

		// return
		return array(
			'chart_alert_interactions' => $chart_alert_interactions,
			'chart_alert_clickthrough_rates' => $chart_alert_clickthrough_rates,
			'chart_alert_category_preferences' => $chart_alert_category_preferences,
		);
	}

	/**
	 * Charts for social
	 *   1) social_shares [line]
	 *   2) social_followers [line]
	 *
	 * @param $hub
	 * @param $startDate
	 * @param $endDate
	 * @return array
	 */
	protected function getSocialData($hub, $startDate, $endDate)
	{
		// chart options
		$lineChartOptions = $this->getLineChartOptions();

		// 1) social_shares [line]
		$platforms = Platform::where('is_active', '=', true)->get();
		$socialShares = SocialReporting::socialShares($hub, $platforms->pluck('platform'), $startDate, $endDate)->get();
		$datasets = array();
		foreach($platforms as $i => $platform) {
			$datasets[] = array_replace_recursive(array(
				'label' => $platform->name,
				'data' => $socialShares->pluck($platform->platform . '_count'),
			), $lineChartOptions['datasetStyles'][$i], $lineChartOptions['datasetStyles']['borders']);
		}
		$chart_social_shares = array(
			'type' => 'line',
			'data' => array(
				'labels' => $socialShares->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// 2) social_followers [line]
		$socialFollowers = SocialReporting::socialFollowers($hub, $startDate, $endDate)->get();
		$datasets = array(
			array_replace_recursive(array(
				'label' => 'All',
				'data' => $socialFollowers->pluck('all_totals'),
			), $lineChartOptions['datasetStyles'][0], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Top 20%',
				'data' => $socialFollowers->pluck('top_totals'),
			), $lineChartOptions['datasetStyles'][1], $lineChartOptions['datasetStyles']['borders']),
			array_replace_recursive(array(
				'label' => 'Bottom 20%',
				'data' => $socialFollowers->pluck('bottom_totals'),
			), $lineChartOptions['datasetStyles'][2], $lineChartOptions['datasetStyles']['borders']),
		);
		$chart_social_followers = array(
			'type' => 'line',
			'data' => array(
				'labels' => $socialFollowers->pluck('end_date'),
				'datasets' => $datasets
			),
			'options' => $lineChartOptions['options']
		);

		// return
		return array(
			'chart_social_shares' => $chart_social_shares,
			'chart_social_followers' => $chart_social_followers,
		);
	}

	/// Section: Chart Options

	/**
	 * Get Line Chart Option for report
	 *
	 * getLineChartOptions
	 * 
	 * @return Array
	 */
	protected function getLineChartOptions()
	{
		$options = array(
			'scales' => array(
				'yAxes' => array(
					array(
						'gridLines' => array(
							'lineWidth' => 1 * 3
						)
					)
				),
				'xAxes' => array(
					array(
						'gridLines' => array(
							'lineWidth' => 1 * 3,
						)
					)
				)
			)
		);
		$datasetStyles = array(
			array(// dataset 1
				'backgroundColor' => 'rgba(75,192,192,0.05)',
				'borderColor' => 'rgba(75,192,192,1)',
			),
			array(// dataset 2
				'backgroundColor' => 'rgba(229,173,2,0.05)',
				'borderColor' => 'rgba(229,173,2,1)',
			),
			array(// dataset 3
				'backgroundColor' => 'rgba(215,37,87,0.05)',
				'borderColor' => 'rgba(215,37,87,1)',
			),
			array(// dataset 4
				'backgroundColor' => 'rgba(215,108,37,0.05)',
				'borderColor' => 'rgba(215,108,37,1)',
			),
			array(// dataset 5
				'backgroundColor' => 'rgba(37,215,54,0.05)',
				'borderColor' => 'rgba(37,215,54,1)',
			),
			array(// dataset 6
				'backgroundColor' => 'rgba(158,37,215,0.05)',
				'borderColor' => 'rgba(158,37,215,1)',
			),
			// common dataset styles
			'borders' => array(
				'borderWidth' => 2 * 3,
				'pointBorderWidth' => 1 * 3,
				'pointRadius' => 3 * 3,
			)
		);
		return array(
			'options' => $options,
			'datasetStyles' => $datasetStyles
		);
	}

	/**
	 * Get Doughnut Chart Option for report
	 *
	 * getDoughnutChartOptions
	 * 
	 * @return Array
	 */
	protected function getDoughnutChartOptions()
	{
		$options = array();
		$datasetStyles = array(
			'backgroundColor' => array(
				'#FF6384',
				'#36A2EB',
				'#FFCE56',
				'#fe8fa7',
				'#6cb9ed',
				'#fedb85',
				'#ffcdd8',
				'#aed5f0',
				'#fbe9be',
				'#feeef2',
				'#dae9f4',
				'#fdf4dd',
			)
		);
		return array(
			'options' => $options,
			'datasetStyles' => $datasetStyles
		);
	}

	/**
	 * Get Bar Chart Options for Report
	 * 
	 * getLineChartOptions
	 *
	 * @return void
	 */
	protected function getBarChartOptions()
	{
		$options = array(
			'scales' => array(
				'yAxes' => array(
					array(
						'ticks' => array(
							'beginAtZero' => true
						),
						'gridLines' => array(
							'lineWidth' => 1 * 3,
							'zeroLineWidth' => 1 * 3
						)
					)
				),
				'xAxes' => array(
					array(
						'gridLines' => array(
							'lineWidth' => 1 * 3,
							'zeroLineWidth' => 1 * 3
						)
					)
				)
			)
		);
		$datasetStyles = array(
			'backgroundColor' => array(
				'#FF6384',
				'#36A2EB',
				'#FFCE56',
				'#fe8fa7',
				'#6cb9ed',
				'#fedb85',
				'#ffcdd8',
				'#aed5f0',
				'#fbe9be',
				'#feeef2',
				'#dae9f4',
				'#fdf4dd',
			),
			'borderColor' => array(
				'#FF6384',
				'#36A2EB',
				'#FFCE56',
				'#fe8fa7',
				'#6cb9ed',
				'#fedb85',
				'#ffcdd8',
				'#aed5f0',
				'#fbe9be',
				'#feeef2',
				'#dae9f4',
				'#fdf4dd',
			),
			'borderWidth' => 1 * 3
		);
		return array(
			'options' => $options,
			'datasetStyles' => $datasetStyles
		);
	}
}
