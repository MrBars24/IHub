<?php

namespace App\Http\Controllers\Hub;

// App
use App\Category;
use App\Http\Controllers\Controller;
use App\Hub;
use App\Gig;
use App\GigFeed;
use App\GigFeedPost;
use App\GigIgnore;
use App\GigPost;
use App\GigAttachment;
use App\PostAttachment;
use App\FileStorage;
use App\Post;
use App\Reward;
use App\User;
use App\Platform;
use App\Http\Requests\Gig\CreateGigRequest;
use App\Http\Requests\Gig\FeedRequest;
use App\Modules\Files\FileManager;

// 3rd party
use Image;
use Bridge;

// Laravel
use Illuminate\Http\Request;

class GigController extends Controller
{

	/**
	 * GET /api/{hub}/gigs
	 * ROUTE hub::gig.list [api.php]
	 *
	 * The hub gig carousel page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function getList(Request $request, Hub $hub, Gig $gig)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		$membership = $auth_user->getMembershipTo($hub);

		// get gigs
		// - in current hub
		// - published (influencer only)
		// - not completed by logged in influencer (influencer only)
		// - @todo favour gigs which match category preferences of the influencer (influencer only)
		$gigs = Gig::with([
				'attachments.file',
				'categories' => function($query) {
					$query->select([
						'category.id',
						'category.name'
					])
					->where('category.is_active', '=', true);
				},
				'platforms' => function($query) {
					$query->select([
						'platform.id',
						'platform.platform',
						'platform.name'
					])
					->where('platform.is_active', '=', true);
				}
			])
			->select([
				'id', 
				'title', 
				'slug', 
				'description', 
				'place_count', 
				'points', 
				'commence_at', 
				'deadline_at',
				'ideas',
				'ideas_facebook',
				'ideas_twitter',
				'ideas_linkedin',
				'ideas_pinterest',
				'ideas_youtube',
				'ideas_instagram'
			]);

		// check if user is influencer
		// then we add influencer specific conditions
		if($membership->role == 'influencer') {

			// get current date to determine "gig urgency"
			// gig urgency is based on time away from the deadline date and will determine if we will display ignored gigs or not:
			// - before 2 days  - 0
			// - within 2 days  - 1
			// - within 6 hours - 2
			$date = carbon()->format('Y-m-d H:i:s');

			// gig ignore derived table
			$gigIgnore = \DB::table('gig_ignore')
				->selectRaw('gig_id, count(user_id) AS ignore_count, ' . "
					(case 
						when '$date' > (deadline_at - INTERVAL 6 HOUR) then 2 
						when '$date' > (deadline_at - INTERVAL 2 DAY) then 1 
						else 0 end
					) AS gig_urgency")
				->join('gig AS g2', 'g2.id', '=', 'gig_ignore.gig_id')
				->where('user_id', '=', $auth_user->id)
				->groupBy('gig_id');

			// join and then order
			$gigs->leftJoin(\DB::raw(' ( ' . $gigIgnore->toSql() . ' ) AS gi '), 'gi.gig_id', '=', 'gig.id')
				->mergeBindings($gigIgnore)
				->orderBy('ignore_count');

			// @todo: need to check for place count here as well

			// find gigs that have not been completed by this influencer
			$gigs->whereNotExists(function($query) use ($auth_user) {
				$query->select(\DB::raw(1))
					->from('gig_post')
					->join('post', 'gig_post.post_id', '=', 'post.id')
					->whereRaw('gig_post.gig_id = gig.id')
					->whereIn('status', ['pending', 'verified', 'scheduled'])
					->where('post.author_id', '=', $auth_user->id)
					->where('post.author_type', '=', User::class);
			})
				->where('is_active', '=', true)
				->where('is_live', '=', true)
				->where(function($query) {
					$query->where('ignore_count', '<=', 'gig_urgency')
						->orWhereNull('ignore_count');
				});
		}
		elseif($membership->role === 'hubmanager') {
			// determine if we will show the `load more` button based from the gigs that:
			// - are not published yet
			// - are expired
			
			// clone gigs query builder
			$moreGigs = clone $gigs;
			$now = carbon();

			$moreGigsCount = $moreGigs->where('hub_id', '=', $hub->id)
				->where(function($query) use ($now) {
					$query->where('is_live', '=', false)
						->orWhere('is_active', '=', false)
						->orWhere('deadline_at', '<=', $now);
						// ->orWhere('has_expired_notified', '=', true);
				})
				->count();

			// get gigs that is'nt expired. 
			// @note: based from GigsNotify@handleGigExpired command
			$gigs->where('is_live', '=', true)
				->where('deadline_at', '>', $now);
				// ->where('has_expired_notified', '=', false);
		}

		// perform query
		$gigs = $gigs
			->orderBy('deadline_at')
			->where('hub_id', '=', $hub->id) // NOTE: can we move this to the base query above?
			->get();

		// response
		$data = [
			'data' => [
				'gigs' => $gigs->toArray(),
			],
			'route' => 'hub::gig.list',
			'success' => true
		];

		if($membership->role === 'hubmanager' && $moreGigsCount > 0) {
			$data['data'] += ['more_gigs_count' => $moreGigsCount];
		}
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gigs/expired
	 * ROUTE hub::gig.list.expired [api.php]
	 *
	 * get the expired gigs list
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getExpired(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();
		$membership = $auth_user->getMembershipTo($hub);
		$now = carbon();

		// checkpoint: for hubmanager only. do this via middleware
		// if ($membership->role !== 'hubmanager') return;

		$gigs = Gig::with([
			'attachments.file',
			'categories' => function($query) {
				$query->select([
					'category.id',
					'category.name'
				])
				->where('category.is_active', '=', true);
			},
			'platforms' => function($query) {
				$query->select([
					'platform.id',
					'platform.platform',
					'platform.name'
				])
				->where('platform.is_active', '=', true);
			}
			])
			->select([
				'id', 
				'title', 
				'slug', 
				'description', 
				'place_count', 
				'points', 
				'commence_at', 
				'deadline_at',
				'ideas',
				'ideas_facebook',
				'ideas_twitter',
				'ideas_linkedin',
				'ideas_pinterest',
				'ideas_youtube',
				'ideas_instagram'
			])
			->where('gig.hub_id', '=', $hub->id)
			->where(function($query) use ($now) {
				$query->where('is_live', '=', false)
					->orWhere('is_active', '=', false)
					->orWhere('deadline_at', '<=', $now);
					// ->orWhere('has_expired_notified', '=', true);
			})
			->orderBy('gig.deadline_at', 'DESC')
			->paginate(24);
		
		// response
		$data = [
			'data' => [
				'gigs' => $gigs
			],
			'route' => 'hub::gig.list.expired',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gig/ignore/{gig}
	 * ROUTE hub::gig.ignore [api.php]
	 *
	 * Ignore the specific gig (influencer only)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function ignore(Request $request, Hub $hub, Gig $gig)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();

		// ignore the gig
		GigIgnore::query()->firstOrCreate([
			'user_id' => $auth_user->id,
			'gig_id' => $gig->id,
		]);

		// response
		$data = [
			'data' => [
			],
			'route' => 'hub::gig.ignore',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gig/{gig}
	 * ROUTE hub::gig.view [api.php]
	 *
	 * The hub gig page (specific gig)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function getGig(Request $request, Hub $hub, Gig $gig)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();

		// build gig query
		$gigs = Gig::with([
			'attachments.file',
			'categories' => function($query) {
				$query->select([
					'category.id',
					'category.name'
				])
					->where('category.is_active', '=', true);
			},
			'platforms' => function($query) {
				$query->select([
					'platform.id',
					'platform.platform',
					'platform.name'
				])
					->where('platform.is_active', '=', true);
			}
		]);

		// then we add influencer specific conditions
		if($auth_user->getMembershipTo($hub)->role == 'influencer') {

			// @todo: need to check for place count here as well

			// find gigs that have not been completed by this influencer
			$gigs->whereNotExists(function($query) use ($auth_user) {
				$query->select(\DB::raw(1))
					->from('gig_post')
					->join('post', 'gig_post.post_id', '=', 'post.id')
					->whereRaw('gig_post.gig_id = gig.id')
					->whereIn('status', ['pending', 'verified', 'scheduled'])
					->where('post.author_id', '=', $auth_user->id)
					->where('post.author_type', '=', User::class);
			})
				->where('is_active', '=', true)
				->where('is_live', '=', true);
		}

		// get the specific gig
		$gig = $gigs
			->where('hub_id', '=', $hub->id)
			->find($gig->id);

		// response
		$data = [
			'data' => [
				'gig' => $gig
			],
			'route' => 'hub::gig.view',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * DELETE /api/{hub}/gig/{gig}
	 * ROUTE hub::gig.delete [api.php]
	 *
	 * perform a soft delete to the model
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function delete(Request $request, Hub $hub, Gig $gig)
	{
		$gig->delete();

		// response
		$data = [
			'data' => [
				
			],
			'route' => 'hub::gig.delete',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gig/create
	 * ROUTE hub::gig.create [api.php]
	 *
	 * The hub gig create page (specific gig)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getCreate(Request $request, Hub $hub)
	{
		// get form dependencies
		$common = $this->getCommonFormData($hub); // map the user's preferred categories in settings?

		// response
		$data = [
			'data' => $common + [
				'default_gig_require_approval' => $hub->default_gig_require_approval
			],
			'route' => 'hub::gig.create',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/gig/create
	 * ROUTE hub::gig.create.save [api.php]
	 *
	 * The hub gig create page (specific gig)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function postCreate(CreateGigRequest $request, Hub $hub)
	{
		$categories = $request->input('categories');
		$platforms = collect($request->input('platforms'));
		$attachments = $request->input('attachments');
		// $rewards = $request->input('rewards');
		$gigData = $request->except(['categories', 'platforms', 'attachments', 'rewards']);
		
		// create gig
		//$gig = Gig::create($gigData);
		
		$gig = new Gig();
		$gig->fill($gigData);
		$gig->hub_id = $hub->id;
		$gig->save();

		// call artisan command gigs:notify to add to queue
		\Artisan::queue('gigs:notify');

		// insert rewards, rewards are already filtered in front-end to remove the empty reward
		//$rewards = $gig->rewards()->createMany($rewards);

		// insert attachments
		foreach($attachments as $attachmentData) {
			$attachment = new GigAttachment;
			$attachment->fill($attachmentData);
			$attachment->hub_id = $hub->id;
			$attachment->gig_id = $gig->id;
			$attachment->save();

			// store file against post attachment
			if($attachmentData['type'] == 'image' || $attachmentData['type'] == 'video') {
				$attachment->storeFile();
			}
		}

		// insert catetgories
		foreach($categories as $category) {
			$gig->categories()->attach($category);
		}

		// insert platforms
		foreach($platforms as $platform) {
			$gig->platforms()->attach($platform['id']);
		}

		// response
		$data = [
			'data' => $gig->toArray(),
			'route' => 'hub::gig.create',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gig/edit/{gig}
	 * ROUTE hub::gig.edit [api.php]
	 *
	 * update Hub gig
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Gig                  $gig
	 * @return \Illuminate\Http\Response
	 */
	public function getEdit(Request $request, Hub $hub, Gig $gig)
	{
		// get form dependencies
		$common = $this->getCommonFormData($hub);

		// load gig relations
		$gig->load([
			'rewards',
			'categories',
			'platforms',
			'attachments.file'
		]);

		// response
		$data = [
			'data' => [
				'commonForms' => $common,
				'gig' => $gig->toArray()
			],
			'route' => 'hub::gig.edit',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * PATCH /api/{hub}/gig/edit/{gig}
	 * ROUTE hub::gig.update [api.php]
	 *
	 * @param CreateGigRequest $request
	 * @param Hub $hub
	 * @param Gig $gig
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(CreateGigRequest $request, Hub $hub, Gig $gig)
	{
		$categories = $request->input('categories');
		$platforms = collect($request->input('platforms'));
		$rewards = $request->input('rewards');
		$attachments = $request->input('attachments');
		$gigData = $request->except(['categories', 'platforms', 'rewards', 'attachments']);

		// save gig
		$gig->fill($gigData);
		$gig->save();

		// call artisan command gigs:notify to add to queue
		\Artisan::queue('gigs:notify');

		// update rewards
		foreach($rewards as $key => $reward) {
			if (isset($reward['id'])) { // check if reward is already exists
				Reward::find($reward['id'])->update($reward);
			}
			else {
				$data = [
					'description' => $reward['description'],
					'gig_id' => $gig->id
				];
				$_reward = Reward::create($data);
				$gig->rewards()->save($_reward);
			}
		}

		// update attachments
		foreach($attachments as $attachmentData) {
			if (isset($attachmentData['id'])) {
				GigAttachment::find($attachmentData['id'])->update($attachmentData);
			}
			else {
				//$attachment = GigAttachment::create($attachmentData);
				$attachment = new GigAttachment($attachmentData);
				$attachment->hub()->associate($hub);
				$gig->attachments()->save($attachment);
				
				// store file against post attachment
				if($attachmentData['type'] == 'image' || $attachmentData['type'] == 'video') {
					$attachment->storeFile();
				}
			}
		}

		// update catetgories
		$gig->categories()->sync($categories);
		// update platforms
		$gig->platforms()->sync($platforms->pluck('id'));

		// response
		$data = [
			'data' => $gig->toArray(),
			'route' => 'hub::gig.create',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * DELETE /api/{hub}/gig/{gig}/remove/attachment/{attachment}
	 * ROUTE hub::gig.delete.attachment [api.php]
	 *
	 * delete gig attachment
	 *
	 * @param  \Illuminate\Http\Request    $request
	 * @param  \App\Hub                    $hub
	 * @param  \App\Gig                    $gig
	 * @param  \App\GigAttachment          $attachment
	 * @return \Illuminate\Http\Response
	 */
	public function deleteAttachment(Request $request, Hub $hub, Gig $gig, GigAttachment $attachment)
	{
		$attachment = $gig->attachments()->find($attachment->id)->delete();

		$data = [
			'data' => [
				'attachment' => $attachment
			],
			'route' => 'hub::gig.delete.attachment',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * DELETE /api/{hub}/gig/{gig}/remove/attachment/{attachment}
	 * ROUTE hub::gig.delete.reward [api.php]
	 *
	 * delete gig attachment
	 *
	 * @param  \Illuminate\Http\Request    $request
	 * @param  \App\Hub                    $hub
	 * @param  \App\Gig                    $gig
	 * @param  \App\GigAttachment          $attachment
	 * @return \Illuminate\Http\Response
	*/
	public function deleteReward(Request $request, Hub $hub, Gig $gig, Reward $reward)
	{
		$reward = $gig->rewards()->find($reward->id)->delete();

		$data = [
			'data' => [
				'reward' => $reward
			],
			'route' => 'hub::gig.delete.reward',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/gig/review
	 * ROUTE hub::gig.review [api.php]
	 *
	 * The gig review page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getReview(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();

		// get gig posts for review
		// - in current hub
		// - not published
		// - that are pending review
		// - with sub posts
		$posts = Post::with([
			'author', // select and order by calls don't work on polymorphic relations
			'attachment',
			'subPosts' => function($query) {
				$query->where('result', '=', 'not ready');
			}
		])
			->join('gig_post', 'post.id', '=', 'gig_post.post_id')
			->where('post.is_published', '=', false)
			->where('post.hub_id', '=', $hub->id)
			->where('gig_post.status', '=', 'pending')
			->select(['post.*', 'gig_post.gig_id'])
			->orderBy('post.created_at')
			->paginate(5);

		// response
		$data = [
			'data' => [
				'posts' => $posts->toArray()
			],
			'route' => 'hub::gig.review',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/gig/accept
	 * ROUTE hub::gig.accept [api.php]
	 *
	 * Accept gig post
	 * expects request payload
	 * [message, post_id, gig_id] // NOTE: please confirm
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function postAccept(Request $request, Hub $hub)
	{
		// get gig post
		$gigPost = GigPost::query()
			->where('gig_id', '=', $request->input('gig_id'))
			->where('post_id', '=', $request->input('post_id'))
			->first();

		// get current time for scheduling
		$now = carbon();

		// accept the post by publishing it, or if it's scheduled into the future, then let the schedule run
		if($gigPost->schedule_result == 'pending' && $gigPost->schedule_at > $now) {
			$gigPost->status = 'scheduled';
			$gigPost->save();
		} else {
			$gigPost->publish();
		}

		// response
		$data = [
			'data' => [
			],
			'route' => 'hub::gig.accept',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/gig/reject
	 * ROUTE hub::gig.reject [api.php]
	 *
	 * Reject gig post
	 * expects request payload
	 * [message, post_id, gig_id] // NOTE: please confirm
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function postReject(Request $request, Hub $hub)
	{
		// get the gig post
		$gigPost = GigPost::query()
			->where('gig_id', '=', $request->input('gig_id'))
			->where('post_id', '=', $request->input('post_id'))
			->first();

		// reject the post
		$gigPost->reject($request->input('rejection_reason'));

		// response
		$data = [
			'data' => [
				'post' => $gigPost
			],
			'route' => 'hub::gig.reject',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/count
	 * ROUTE: hub::gig.my.count [api.php]
	 * 
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getTotalMyGigCount(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();
		$role = $auth_user->getMembershipTo($hub)->role;

		// base posts
		$posts = \DB::table('post')
            ->join('gig_post', 'post.id', '=', 'gig_post.post_id')
			->where('post.hub_id', '=', $hub->id)
			->select([
				'gig_post.status', 
				'gig_post.schedule_result',
				'post.is_published'
			]);
			
		if ($role === 'influencer') {
			$posts = $posts->where('post.author_id', '=', $auth_user->id)
				->where('post.author_type', '=', get_class($auth_user));
		}

		$posts = $posts->get();
		
		// NOTE: i'm not using the query builder because 
		// it's mutating the following posts query..

		// rejected posts
		$rejected = $posts->filter(function($item) {
			return $item->status == 'rejected';
		})->count();

		// scheduled posts
		$scheduled = $posts->filter(function($item) {
			return $item->status == 'scheduled' && $item->schedule_result == 'pending';
		})->count();
		
		// pending posts
		$pending = $posts->filter(function($item) {
			return $item->status == 'pending' && $item->is_published == false;
		})->count();
		
		$data = [
			'rejected' => $rejected,
			'scheduled' => $scheduled,
			'pending' => $pending
		];

		// add the gig feed posts if user is a hubmanager
		if ($role === 'hubmanager') {
			$gig_feeds = \DB::table('gig_feed_post')
				->where('hub_id', '=', $hub->id)
				->count();

			$data['feeds_list'] = $gig_feeds;
		}

		// set the active tab
		$active_tab = 'scheduled';
		foreach($data as $key => $count) {	
			if ($count > 0) {
				$active_tab = $key;
				break;
			}
		}
		$data['active_tab'] = $active_tab;


		// response
		$data = [
			'data' => $data,
			'route' => 'hub::gig.my.count',
			'success' => true
		];
		return response()->json($data);
		
	}

	/**
	 * GET /{hub}/mygigs/scheduled
	 * ROUTE: hub::gig.my.scheduled [api.php]
	 * 
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getScheduled(Request $request, Hub $hub)
	{
		// show gig posts that are scheduled influencer
		// - gig_post.status = scheduled
		// - gig_post.schedule_result = pending
		// - order by latest schedule date ? // please confirm

		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();

		$posts = Post::with(Post::withComponents())
			->join('gig_post', 'post.id', '=', 'gig_post.post_id')
			->where('post.hub_id', '=', $hub->id)
			->where('gig_post.status', '=', 'scheduled')
			->where('gig_post.schedule_result', 'pending')
			->orderBy('gig_post.schedule_at')
			->select(['post.*', 'gig_post.schedule_at', 'gig_post.gig_id']);
		
		// - check if influencer
		// - get the post made by the influencer
		if ($auth_user->getMembershipTo($hub)->role === 'influencer') {
			$posts->where('post.author_id', '=', $auth_user->id)
				->where('post.author_type', '=', get_class($auth_user));
		}

		// response
		$data = [
			'data' => [
				'posts' => $posts->paginate(10)
			],
			'route' => 'hub::gig.my.scheduled',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/scheduled/{post}/reschedule
	 * ROUTE: hub::gig.my.scheduled.reschedule [api.php]
	 * 
	 * @param \Illuminate\Http\Request   $request
	 * @param \App\Hub\                  $hub
	 * @param \App\Post                  $post
	 * @return \Illuminate\Http\Response
	 */
	public function reschedulePost(Request $request, Hub $hub, Post $post)
	{
		$gigPost = GigPost::where('post_id', $post->id)
					->where('gig_id', '=', $request->input('gig_id')) // NOTE: we can omit this.
					->first();

		$gigPost->schedule($request->input('schedule_at'));

		// response
		$data = [
			'data' => [
				'gig_post' => $gigPost
			],
			'route' => 'hub::gig.my.scheduled.reschedulePost',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/scheduled/{post}/cancel
	 * ROUTE: hub::gig.my.scheduled.cancel [api.php]
	 * set the post.status to 'superceded'
	 * 
	 * @param \Illuminate\Http\Request   $request
	 * @param \App\Hub\                  $hub
	 * @param \App\Post                  $post
	 * @return \Illuminate\Http\Response
	 */
	public function cancelPost(Request $request, Hub $hub, Post $post)
	{
		$gigPost = GigPost::where('post_id', $post->id)
					->where('gig_id', '=', $request->input('gig_id')) // NOTE: we can omit this.
					->first();

		$cancel = $gigPost->cancel();
		
		// response
		$data = [
			'data' => [
				'gig_post' => $gigPost
			],
			'route' => 'hub::gig.my.scheduled.cancel',
			'success' => $cancel
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/rejected
	 * ROUTE: hub::gig.my.rejected [api.php]
	 * 
	 * @param Request $request
	 * @param Hub $hub
	 * @return [type] [description]
	 */
	public function getRejected(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();
		// show gig posts that are rejected by hub manager for logged in influencer
		// - gig_post.status = rejected
		// - order by gig_post.created_at
		$posts = Post::with(Post::withComponents())
			->join('gig_post', 'post.id', '=', 'gig_post.post_id')
			->where('post.hub_id', '=', $hub->id)
			->where('gig_post.status', '=', 'rejected')
			->orderBy('gig_post.created_at')
			->select('post.*');
		
		// - check if influencer
		// - get the post made by the influencer
		if ($auth_user->getMembershipTo($hub)->role === 'influencer') {
			$posts->where('post.author_id', '=', $auth_user->id)
				->where('post.author_type', '=', get_class($auth_user));
		}

		// response
		$data = [
			'data' => [
				'posts' => $posts->paginate(10)
			],
			'route' => 'hub::gig.my.rejected',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/approval
	 * ROUTE: hub::gig.my.approval [api.php]
	 * 
	 * @param Request $request
	 * @param Hub $hub
	 * @return [type] [description]
	 */
	public function getApproval(Request $request, Hub $hub)
	{
		// show gig posts that are waiting to be approved by hub manager
		// - gig_post.status = pending
		// - order by latest created gig_post desc
		$posts = Post::with([
			'author', // select and order by calls don't work on polymorphic relations
			'attachment',
			'subPosts' => function($query) {
				$query->where('result', '=', 'not ready');
			}
		])
			->join('gig_post', 'post.id', '=', 'gig_post.post_id')
			->where('post.is_published', '=', false)
			->where('post.hub_id', '=', $hub->id)
			->where('gig_post.status', '=', 'pending')
			->select(['post.*', 'gig_post.gig_id', 'gig_post.schedule_at'])
			->orderBy('post.created_at')
			->paginate(10);

		// response
		$data = [
			'data' => [
				'posts' => $posts
			],
			'route' => 'hub::gig.my.approval',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/feed
	 * ROUTE: hub::gig.my.feed [api.php]
	 */
	public function getGigFeeds(Request $request, Hub $hub)
	{
		// get gig feed posts
		$posts = GigFeedPost::query()
			->where('hub_id', '=', $hub->id)
			->orderBy('created_at', 'DESC')
			->paginate(20);
		
		// response
		$data = [
			'data' => [
				'feeds' => $posts
			],
			'route' => 'hub::gig.my.feed',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/feed/manage
	 * ROUTE: hub::gig.my.feed.manage [api.php]
	 */
	public function getFeedConfigList(Request $request, Hub $hub)
	{
		// get feeds configured by hub
		$feeds = GigFeed::query()
			->where('hub_id', '=', $hub->id)
			->orderBy('is_active', 'DESC')
			->get();

		// response
		$data = [
			'data' => [
				'feeds' => $feeds
			],
			'route' => 'hub::gig.my.feed.manage',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /{hub}/mygigs/feed/manage/edit/{gig_feed}
	 * ROUTE: hub::gig.my.feed.manage.edit [api.php]
	 */
	public function getFeedConfig(Request $request, Hub $hub, GigFeed $gig_feed)
	{
		// get gig feed details
		$feed = GigFeed::query()
			->where('hub_id', '=', $hub->id)
			->find($gig_feed->id);

		// response
		$data = [
			'data' => [
				'feed' => $feed
			],
			'route' => 'hub::gig.my.feed.manage.edit',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * PATCH /{hub}/mygigs/feed/manage/edit/{gig_feed}
	 * ROUTE: hub::gig.my.feed.manage.update [api.php]
	 */
	public function updateFeedConfig(FeedRequest $request, Hub $hub, GigFeed $gig_feed)
	{
		// get gig feed
		$feed = GigFeed::query()
			->where('hub_id', '=', $hub->id)
			->find($gig_feed->id);

		// update
		$feed->update($request->except([
			'object_class',
			'id'
		]));
		
		// response
		$data = [
			'data' => [
				'feed' => $feed
			],
			'route' => 'hub::gig.my.feed.manage.update',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /{hub}/mygigs/feed/post/{feed_post}
	 * ROUTE: hub::gig.my.feed.post [api.php]
	 */
	public function createFeedPostContext(Request $request, Hub $hub, GigFeedPost $feed_post)
	{
		$context = $request->input('context'); // gig or post

		// create a gig
		if($context == 'gig') {
			$response = $this->handleCreateGig($request, $hub, $feed_post);
		}
		// create a post
		else {
			$response = $this->handleCreatePost($request, $hub, $feed_post);
		}

		$message = 'New Gig has been created.';
		if ($response instanceof Post) {
			$message = 'New Post has been created.';
		}

		// response
		$data = [
			'data' => [
				'data' => $response,
				'message' => $message
			],
			'route' => ' hub::gig.my.feed.post',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * Create a new Gig object from the GigFeedPost
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\GigFeedPost          $feed_post
	 * @return \App\Gig
	 */
	private function handleCreateGig(Request $request, Hub $hub, GigFeedPost $feed_post)
	{
		// calculate commence_at and deadline_at
		$commence_at_minute_x = carbon()->minute;
		$commence_at_minute_y = ceil($commence_at_minute_x / 15) * 15;
		$commence_at = carbon()->addMinutes($commence_at_minute_y - $commence_at_minute_x)->second(0);

		// calculate deadline_at
		$deadline_at_minute_x = carbon()->minute;
		$deadline_at_minute_y = ceil($deadline_at_minute_x / 15) * 15;
		$deadline_at = carbon()->addMinutes($deadline_at_minute_y - $deadline_at_minute_x)->second(0);
		$deadline_at = $deadline_at->addWeeks(2);

		// create basic details from the GigFeedPost
		$gig = new Gig;
		$gig->title = $request->input('title');
		$gig->description = 'Please help us spread the word and share to your social networks.'; // default word if the gig is created from gig feed process.
		$gig->ideas = fix_links($feed_post->description, '#, @'); // @todo: added html2text here
		$gig->points = $request->input('points', 10); // minimum points of a gig (should set this as a default value in migration.)
		$gig->place_count = 3; // minimum place_count of a gig (should set this as a default value in migration.)
		$gig->hub()->associate($hub);
		$gig->commence_at = $commence_at;
		$gig->deadline_at = $deadline_at;
		$gig->save();
		
		// insert platforms
		$platforms = collect($request->input('platforms', []));
		foreach($platforms as $platform) {
			$gig->platforms()->attach($platform['id']);
		}

		// create attachment
		if(!is_null($feed_post->thumbnail)) {
			// NOTE: should we copy file from gigfeedpost and recreate the filename ?
			// or use the same file existed from the gigfeedpost ?..
			$oldFileStorage = $feed_post->file;

			// create file storage by creating a new staged object
			$newFileStorage = new FileStorage();
			$newFileStorage->path = $oldFileStorage->path;
			$newFileStorage->status = 'staged';
			$newFileStorage->save();
			
			$attachment = GigAttachment::create([
				'resource' => $feed_post->thumbnail,
				'title' => $feed_post->thumbnail,
				'type' => 'image'
			]);
			// store and move the file
			$attachment->hub()->associate($hub);
			$attachment->storeFile();
			$gig->attachments()->save($attachment);
		}

		return $gig;
	}

	/**
	 * Create a new Post object from the GigFeedPost
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub          $hub
	 * @param  \App\GigFeedPost  $feed_post
	 * @return \App\Post
	 */
	private function handleCreatePost(Request $request, Hub $hub, GigFeedPost $feed_post)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();//auth()->user();
		
		// get entity
		$author = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager')  {
			$author = $hub;
		}
		
		$post = new Post;
		$post->message = fix_links($feed_post->description, ['#','@']); // store null to prevent cache value being overwritten. we can use convert_html_to_text LATER if we need to
		$post->message_cached = $feed_post->description; // add cached message here, since we are receiving the rendered post, not plain text
		$post->hub()->associate($hub);
		$post->author()->associate($author);
		$post->is_published = true;
		$post->save();

		// create attachment
		if(!is_null($feed_post->thumbnail)) {
			// NOTE: should we copy file from gigfeedpost and recreate the filename ?
			// or use the same file existed from the gigfeedpost ?..
			// get old file storage so we can get full file path
			$oldFileStorage = $feed_post->file;

			// create file storage by creating a new staged object
			$newFileStorage = new FileStorage();
			$newFileStorage->path = $oldFileStorage->path;
			$newFileStorage->status = 'staged';
			$newFileStorage->save();
			
			$attachment = PostAttachment::create([
				'resource' => $feed_post->thumbnail,
				'title' => $feed_post->thumbnail,
				'type' => 'image'
			]);
			$attachment->hub()->associate($hub);
			// store and move the file
			$attachment->storeFile();
			$post->attachment()->save($attachment);
		}

		return $post;
	}

	/**
	 * POST /{hub}/mygigs/feed/validate
	 * ROUTE: hub::gig.my.feed.validate [api.php]
	 * 
	 * try to validate the rss link
	 * 
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\Hub                 $hub
	 * @return \Illuminate\Http\Response	 * 
	 */
	public function validateRssLink(Request $request, Hub $hub)
	{
		// non-namespaced proejct doesn't work with namespaced project.
		$bridgePath = app_path('Modules/RssBridge/Bridges/');
		Bridge::setDir($bridgePath);
		$rssBridge = Bridge::create('Rss');
		$message = null;
		try {
			$rssBridge->parseData($request->input('url'));
			$success = true;
			$message = 'Valid Rss feed URL.';
		}
		catch (\Exception $e) {
			$success = false;
			$message = 'Invalid RSS feed URL.';
		}

		// response
		$data = [
			'message' => $message,
			'route' => 'hub::gig.my.feed.validatestore',
			'success' => $success
		];
		return response()->json($data);
	}

	/**
	 * POST /{hub}/mygigs/feed/manage
	 * ROUTE: hub::gig.my.feed.manage.store [api.php]
	 */
	public function createFeedConfig(FeedRequest $request, Hub $hub)
	{
		// save gig feed
		$data = $request->all();
		$data['hub_id'] = $hub->id;
		$gigFeed = GigFeed::create($data);
		// evaluate the url in saved event.

		// response
		$data = [
			'data' => [
				'feed' => $gigFeed
			],
			'route' => 'hub::gig.my.feed.manage.store',
			'success' => true
		];
		return response()->json($data);
	}
	
	/**
	 * GET /{hub}/mygigs/platforms
	 * ROUTE: hub::gig.my.platforms [api.php]
	 * 
	 * @param  \Illuminate\Http\Request $request
	 * @param  \App\Hub                 $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getPlatforms(Request $request, Hub $hub)
	{
		$commonForms = $this->getCommonFormData($hub);

		// response
		$data = [
			'data' => [
				'platforms' => $commonForms['platforms']
			],
			'route' => 'hub::gig.my.feed.manage.store',
			'success' => true
		];
		
		return response()->json($data);
	}

	/// Section: Helpers

	/**
	 * Return common dependencies bvetween create and edit gig end points
	 *
	 * @param \App\Hub $hub
	 * @return array
	 */
	private function getCommonFormData(Hub $hub)
	{
		// get categories
		// - active only
		// - order alphabetically
		$categories = Category::query()
			->where('is_active', '=', true)
			->where('hub_id', '=', $hub->id)
			->orderBy('name')
			->get();
		// $categories = $hub->categories->toArray(); // TODO: create category hub relation 

		// get platforms
		// - active only
		$platforms = Platform::query()
			->where('is_active', '=', true)
			->get();

		return [
			'categories' => $categories->toArray(),
			'platforms' => $platforms->toArray()
		];
	}
}
