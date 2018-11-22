<?php

namespace App\Http\Controllers\Hub;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Membership;

// Laravel
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
	/**
	 * GET /api/{hub}/leaderboard
	 * ROUTE hub::leaderboard [api.php]
	 *
	 * The leaderboard page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getLeaderboard(Request $request, Hub $hub)
	{
		// get loaderboard data
		// - active users (influencers)
		// - in current hub
		// - order by points (higher the points, higher the rank)
		$leaderboard = Membership::with([
			'user' => function($query) {
				$query->where('is_active', '=', true);
			}
		])
			->select([
				'id', 'hub_id', 'user_id', 'points'
			])
			->where('hub_id', '=', $hub->id)
			->where('role', '=', 'influencer')
			->where('is_active', '=', true) // NOTE: temporary
			->orderBy('points', 'DESC')
			->get();

		// response
		$data = [
			'data' => [
				'leaderboard' => $leaderboard->toArray()
			],
			'route' => 'hub::leaderboard',
			'success' => true
		];
		return response()->json($data);
	}
}
