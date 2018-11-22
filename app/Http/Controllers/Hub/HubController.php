<?php

namespace App\Http\Controllers\Hub;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Post;

// Laravel
use Illuminate\Http\Request;

class HubController extends Controller
{
	/**
	 * GET /api/{hub}/about
	 * ROUTE hub::hub.profile [api.php]
	 *
	 * The hub profile page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getProfile(Request $request, Hub $hub)
	{

		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// check point if hub is active
		// get hub posts
		// - published posts
		// - in current hub
		// - authored by hub
		// - order by most recent
		// - load report status
		// - load hidden status
		$posts = Post::with(Post::withComponents())
			->where('hub_id', '=', $hub->id)
			->where('author_id', '=', $hub->id)
			->where('author_type', '=', Hub::class)
			->where('is_published', '=', true)
			->orderBy('created_at', 'DESC');

		// load postreports for hubmanagers
		if ($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$posts->with(['reports']);
		}
		// infuencer: check if posts were hidden by you.
		else {
			$posts->with([
				'hiddenPosts' => function($query) use($auth_user) {
					$query->where('post_hide.user_id', '=', $auth_user->id)
					->where('post_hide.is_hidden', '=', true);
				},
				'reports' => function($query) use($auth_user) {
					$query->where('post_report.user_id', '=', $auth_user->id);
				}
			]);
		}
		
		// expects hub user
		$user = $hub->getOwners()->first();
		$user->load([
			'membership' => function($query) use ($hub) {
				$query->select([
					'user_id', 'points', 'role'
				])
					->where('hub_id', '=', $hub->id);
			},
			'accounts'
		]);
		
		$hub->accounts = $user->accounts;
		$hub->membership = $user->membership;

		// response
		$data = [
			'data' => [
				'hub' => $hub->toArray(),
				'posts' => $posts->paginate(12)
			],
			'route' => 'hub::user.profile',
			'success' => true
		];
		return response()->json($data);
	}
}
