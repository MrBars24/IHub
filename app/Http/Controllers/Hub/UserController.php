<?php

namespace App\Http\Controllers\Hub;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Post;
use App\User;
use App\GigPost;
use App\Gig;
use App\Entity;

// Laravel
use Illuminate\Http\Request;

class UserController extends Controller
{
	/**
	 * GET /api/{hub}/{user}
	 * ROUTE hub::user.profile [api.php]
	 *
	 * The user profile page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\User                 $user
	 * @return \Illuminate\Http\Response
	 */
	public function getProfile(Request $request, Hub $hub, User $user)
	{
		// @todo: policy for user - is_active
		
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get user posts
		// - published posts
		// - in current hub
		// - authored by user
		// - order by most recent
		// - load components
		// - load report status
		// - load hidden status
		$posts = Post::with(Post::withComponents())
			->where('hub_id', '=', $hub->id)
			->where('author_id', '=', $user->id)
			->where('author_type', '=', User::class)
			->where('is_published', '=', true)
			->orderBy('created_at', 'DESC');
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

		// eager load hidden model attributes
		$user->makeVisible([
			'cover_picture', 
			'summary'
		]);

		// get user info
		// - points from current membership
		// - linked accounts
		$user->load([
			'membership' => function($query) use ($hub) {
				$query->select([
					'user_id', 'points', 'role'
				])
					->where('hub_id', '=', $hub->id);
			},
			'accounts'
		]);

		// get user gig metrics
		// - number of gig_post created by user divided by total number of gigs
		// get gig
		$gigs = Gig::where('hub_id', $hub->id)->count();

		// get gig_post
		$gig_posts = GigPost::leftJoin('post', 'gig_post.post_id', '=', 'post.id')
			->where('post.is_published', '=', true)
			->where('post.hub_id', '=', $hub->id)
			->where('post.author_id', '=', $user->id)
			->where('post.author_type', '=', User::class)
			->count();

		// use divide function to get around divide by zero error
		$gig_metrics = divide($gig_posts, $gigs) * 100; // ceil

		// response
		$data = [
			'data' => [
				'user' => $user->toArray(),
				'posts' => $posts->paginate(12),
				'gig_metrics' => ceil($gig_metrics)
			],
			'route' => 'hub::user.profile',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/entity/search
	 * ROUTE hub::entity.search[api.php]
	 *
	 * Search for entities using the specific keyword(s)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function searchEntity(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get behalf based on auth user
		$behalf = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$behalf = $hub;
		}

		// get params
		$keywords = $request->input('query');

		// get matching entities
		$entities = User::query()
			->select(['profile_picture', 'name', 'id', 'slug', 'filesystem'])
			->where(function($query) use ($keywords) {
				$query->where('name', 'like', "% $keywords%")
					->orWhere('name', 'like', "$keywords%")
					->orWhere('slug', 'like', "$keywords%");
			})
			->where('is_master', '=', false);

		// if behalf is a user, then exclude from results
		if(get_class($behalf) == User::class) {
			$entities->where('id', '<>', $behalf->id);
		}
		$entities = $entities->take(6)->get();

		// prepend hub if behalf is not a hub
		if(get_class($behalf) != Hub::class) {
			$entities = $entities->prepend($hub)->take(6);
		}

		// response
		$data = [
			'data' => $entities->toArray()
		];
		return response()->json($data);
	}
}
