<?php

namespace App\Http\Controllers\Hub;

// App
use App\FacebookAccess;
use App\LinkedinAccess;
use App\FileStorage;
use App\PinterestAccess;
use App\Http\Controllers\Controller;
use App\Gig;
use App\GigPost;
use App\Hub;
use App\Like;
use App\PointAccrual;
use App\YoutubeCategory;
use App\Post;
use App\PostDispatchQueueItem;
use App\PostAttachment;
use App\SocialEntity;
use App\Comment;
use App\PostHide;
use App\PostReport;
use App\Events\Posts\PostReported;

// Laravel
use Illuminate\Http\Request;

class PostController extends Controller
{	
	/**
	 * GET /api/{hub}/newsfeed
	 * ROUTE hub::post.feed [api.php]
	 *
	 * The hub newsfeed page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getFeed(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		
		// get posts in newsfeed
		// - published posts
		// - in current hub
		// - paginated (infinity scroll)
		// - load components
		// - load report status
		// - load hidden status
		$posts = Post::with(Post::withComponents())
			->where('hub_id', '=', $hub->id)
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

		// response
		$data = [
			'data' => [
				'posts' => $posts->paginate(5)
			],
			'route' => 'hub::post.feed',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/post/write
	 * ROUTE hub::post.write [api.php]
	 *
	 * The hub post create (post authoring page)
	 * Expected query string parameters:
	 * - gig (integer, optional)
	 * - post (integer, optional)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function write(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$auth_user->load([
			'accounts' => function($query) {
					$query->where('is_enabled', '=', true);
				}
		]);
		$accounts = $auth_user->accounts->keyBy('platform');

		// get context
		$context = 'newsfeed';

		// get social accounts
		$platforms = [];

		// facebook
		$platform = 'facebook';
		if(isset($accounts[$platform])) {
			$profiles = FacebookAccess::query()
				->leftJoin('linked_account', 'facebook_access.linked_id', '=', 'linked_account.id')
				->where(function($query) use ($auth_user) {
					$query->where('linked_account.user_id', '=', $auth_user->id)
						->orWhereNull('linked_account.user_id');
				})
				->where('facebook_access.parent_id', '=', $accounts[$platform]->native_id)
				->where('facebook_access.is_active', '=', true)
				->where('facebook_access.type', '=', 'page') // temporary remove profiles on list of facebook
				->orderBy('facebook_access.display_order')
				->orderBy('facebook_access.name')
				->select('facebook_access.*', 'linked_account.expired_at', 'linked_account.expired_reason')
				->get();
			$platforms[$platform] = [
				'list' => $profiles
			];
		}

		// twitter
		$platform = 'twitter';
		if(isset($accounts[$platform])) {
			$platforms[$platform] = [
				'list' => [$accounts[$platform]]
			];
		}

		// linkedin
		$platform = 'linkedin';
		if(isset($accounts[$platform])) {
			$profiles = LinkedinAccess::query()
				->leftJoin('linked_account', 'linkedin_access.linked_id', '=', 'linked_account.id')
				->where(function($query) use ($auth_user) {
					$query->where('linked_account.user_id', '=', $auth_user->id)
						->orWhereNull('linked_account.user_id');
				})
				->where('linkedin_access.parent_id', '=', $accounts[$platform]->native_id)
				->where('linkedin_access.is_active', '=', true)
				->orderBy('linkedin_access.display_order')
				->orderBy('linkedin_access.name')
				->select('linkedin_access.*', 'linked_account.expired_at', 'linked_account.expired_reason')
				->get();
			$platforms[$platform] = [
				'list' => $profiles
			];
		}

		// pinterest
		$platform = 'pinterest';
		if(isset($accounts[$platform])) {
			$profiles = PinterestAccess::query()
				->where('parent_id', '=', $accounts[$platform]->native_id)
				->where('is_active', '=', true)
				->orderBy('display_order')
				->orderBy('name')
				->get();
			$platforms[$platform] = [
				'list' => $profiles
			];
		}

		// youtube
		$platform = 'youtube';
		if(isset($accounts[$platform])) {
			$platforms[$platform] = [
				'list' => [$accounts[$platform]]
			];
		}

		// instagram
		$platform = 'instagram';
		if(isset($accounts[$platform])) {
			$platforms[$platform] = [
				'list' => [$accounts[$platform]]
			];
		}

		// get optional gig information
		$gigId = $request->input('gig');
		$gig = null;
		if($gigId) {
			$context = 'gig';
			$gig = Gig::with([
				'attachments.file',
				'platforms'
			])
				->where('is_active', '=', true)
				->where('id', '=', $gigId)
				->first();
		}

		// get optional post information
		$postId = $request->input('post');
		$post = null;
		if($postId) {
			$context = 'share';
			$post = Post::with([
				'attachment'
			])
				->where('is_published', '=', true)
				->where('id', '=', $postId)
				->first();

			// this is only temporary since we're still using the hasOne relationship
			$attachments = PostAttachment::query()
				->where('post_id', '=', $post->id)
				->where('hub_id', '=', $hub->id)
				->get();

			$post->setRelation('attachments', $attachments);
		}

		// enable / disable platforms based on gig's available platforms
		if(!is_null($gig) && !is_null($gig->platforms)) {
			$checks = $gig->platforms->keyBy('platform');
			foreach($platforms as $platform => $info) {
				// if not found, then flag as disabled
				if(!isset($checks[$platform])) {
					$platforms[$platform]['disabled'] = true;
				}
			}
		}

		// get list of youtube categories
		$youtubeCategories = YoutubeCategory::where('is_active', '=', true)
			->select(['title', 'native_id'])
			->get();

		// response
		$data = [
			'data' => [
				'gig' => $gig,
				'post' => $post,
				'platforms' => $platforms,
				'context' => $context,
				'platform_fields' => [
					'youtube' => [
						'categories' => $youtubeCategories
					]
				]
			],
			'route' => 'hub::post.write',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/post/tag/{account?}
	 * ROUTE hub::post.tag
	 *
	 * Show tag suggestions for the specified keyword(s)
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  string|integer            $native_id
	 * @return \Illuminate\Http\Response
	 */
	public function tag(Request $request, Hub $hub, $native_id)
	{
		/**
		 * - detect platform
		 * TODO: do a dynamic class call for the platformconnection class
		 * use {platform}Connection (ie: FacebookConnection)
		 */
		$searchTerm = $request->input('query');
		$platform = $request->input('platform', null);

		// just to make sure that the platform isnt null
		if(is_null($platform)) {
			return response()->json([
				'data' => []
			]);
		}

		// get tag items
		$platformName = ucfirst($platform);
		$class = "\\App\\".$platformName."Connection";
		$taggables = $class::query()
			->select([
				'native_id',
				'profile_id',
				'display_name',
				'screen_name',
				'avatar',
				'type'
			])
			->where(function($query) use ($searchTerm) {
				$query->where('display_name', 'like', '% ' . $searchTerm . '%')
					->orWhere('display_name', 'like', $searchTerm . '%')
					->orWhere('screen_name', 'like', $searchTerm . '%');
			})
			->where('native_id', '=', $native_id)
			->where('is_active', '=', true)
			->limit(6)
			->get();

		// response
		$data = [
			'data' => $taggables->toArray()
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/write
	 * ROUTE hub::post.create [api.php]
	 *
	 * The hub post create (post submission processing)
	 * Expected query string parameters:
	 * - gig (integer, optional)
	 * - post (integer, optional)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$responseMessage = 'Your post has been published.'; // response message

		// get entity
		$author = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager')  {
			$author = $hub;
		}

		// if this post is a share, then just retrieve that post
		$postId = $request->input('post');
		if($postId) {
			$post = Post::with([
				'attachment'
			])
				->where('is_published', '=', true)
				->where('id', '=', $postId)
				->first();

			$responseMessage = 'The post has been shared.';
		}
		// create the post
		else {
			$postData = [
				'message' => $request->input('message'),
			];
			$post = new Post($postData);

			// check type post
			$post->is_published = true;

			// associate the hub, author and save post
			$post->hub()->associate($hub);
			$post->author()->associate($author);
			$post->save();

			// create and associate the attachment
			$attachmentsData = $request->input('attachments', []);

			foreach($attachmentsData as $attachmentData) {
				// SP 10/04: temporary fix to ensure attachment data is not null
				if(is_null($attachmentData)) {
					continue;
				}

				// is a gig post
				if(isset($attachmentData['object_class']) && $attachmentData['object_class'] == 'GigAttachment') {

					if($attachmentData['type'] == 'image' || $attachmentData['type'] == 'video') {

						// get old file storage so we can get full file path
						$oldFileStorage = FileStorage::query()
							->where('id', '=', $attachmentData['file']['id']) 
							->where('object_id', '=', $attachmentData['file']['object_id'])
							->where('object_type', '=', $attachmentData['file']['object_type'])
							->first();

						// create file storage by creating a new staged object
						$newFileStorage = new FileStorage();
						$newFileStorage->path = $oldFileStorage->path;
						$newFileStorage->status = 'staged';
						$newFileStorage->save();

						// create attachment object
						$newAttachmentData = [
							'path' => $attachmentData['resource'],
							'resource' => $attachmentData['resource'], // @todo: this is so tacky, how to deal with not needing this?
							'type' => $attachmentData['type'],
						];

						
						$attachmentData = $newAttachmentData;
					}
				}
				
				$attachment = new PostAttachment;
				$attachment->fill($attachmentData);
				$attachment->hub_id = $hub->id;
				$attachment->post_id = $post->id;
				$attachment->save();

				// store file against post attachment
				if($attachmentData['type'] == 'image' || $attachmentData['type'] == 'video') {
					$attachment->storeFile();
				}
			}
		}

		// get context of this posting
		$context = $request->input('context');

		// social sharing (for each social account selected)
		$nativeIds = $request->input('platform_post_id', []);
		$posts = $request->input('platform_post', []);

		if(!empty($nativeIds)) {

			// get social entities
			// @todo: rewrite this query to ensure cross platform ID clashing can be evaded (eg: facebook and linkedin having the same native_id)
			$socialEntities = SocialEntity::query()
				->whereIn('native_id', $nativeIds)
				->get();
			
			// get post attachments
			$postAttachments = PostAttachment::where('post_id', $post->id)
				->select('id')
				->get();

			if(!$socialEntities->isEmpty()) {
				$list = $socialEntities->keyBy('native_id');
				foreach($posts as $postData) {

					// post to social media
					$attachmentIndex = $postData['attachment_index'];
					$attachment = $attachmentIndex > -1 ? $postAttachments->get($attachmentIndex) : null;
					unset($postData['attachment_index']); // remove the attachment_index from $params data

					$params = $postData;
					$nativeId = $postData['native_id'];

					// account not found; skip
					if(!isset($list[$nativeId])) {
						continue;
					}

					// dispatch or prepare post by queueing for backend job
					if($context == 'gig') {
						$list[$nativeId]->preparePost($post, $auth_user, $context, $params, $attachment);
					} else {
						$list[$nativeId]->dispatchPost($post, $auth_user, $context, $params, $attachment);
					}
				}
			}
		}

		// if this post is for a gig, then we'll also create a "gig_post" record and go through the gig workflow
		$gigId = $request->input('gig');
		if($gigId) {
			$posts = $request->input('platform_post', []);
			// $schedule = $request->input('schedule_options', []);
			$schedule = $request->input('scheduled_at');
			$gig = Gig::find($gigId);
			$gigPost = new GigPost;
			$gigPost->gig()->associate($gig);
			$gigPost->post()->associate($post);
			$gigPost->params = $posts;
			$gigPost->save();

			// publish logic
			// gig requires approval : $gigPost->waitForReview()
			if($gig->require_approval) { // @todo: We'll need to re-enable this soon
				$gigPost->waitForReview($schedule); // nullable schedule date
				$post->unPublished(); // leave post unpublished
				$responseMessage = "Your post will appear in the Newsfeed after the Hub Manager approves your post.";
			}
			// schedule options : $gigPost->schedule()
			elseif(!is_null($schedule)) {
				// $gigPost->schedule($schedule['datetime']);
				$gigPost->schedule($schedule);
				$post->unPublished(); // leave post unpublished
				$schedule = carbon($schedule)->format('D, M d, h:iA');
				$responseMessage = "Your post will appear in the Newsfeed after the scheduled time at $schedule.";
			}
			// no schedule options : $gigPost->publish()
			else {
				$gigPost->publish();
				\Artisan::queue('posts:dispatch');
				$responseMessage = "You have completed the gig \"{$gig->title}\" and earnt {$gig->points} points.";
			}
		}
		// publish post
		else {
			\Artisan::queue('posts:dispatch');
			// accrue points for influencer
			if($auth_user->getMembershipTo($hub)->role === 'influencer') {
				if($context == 'share') {
					$auth_user->membership->accruePoints(PointAccrual::POINTS_POST_SHARE, 'postshare', $post);
				} else {
					$auth_user->membership->accruePoints(PointAccrual::POINTS_POST_NEW, 'postnew', $post);
				}
			}
		}

		// fire event: event.post.shared
		// - only if post author is not the same as the comment author
		// - context: share
		if(!$author->is($post->author) && $context == 'share') {
			$recipients = collect([$post->author]);
			event('event.post.shared', ['event' => 'event.post.shared', 'post' => $post, 'actor' => $author, 'hub' => $hub, 'recipients' => $recipients]);
		}
		
		// response
		$data = [
			'data' => [
				'post' => $post->toArray(),
				'message' => $responseMessage
			],
			'route' => 'hub::post.create',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/post/{post}
	 * ROUTE hub::post.view [api.php]
	 *
	 * The hub post page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function getPost(Request $request, Hub $hub, Post $post)
	{
		// @todo: policy for post - is_published

		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		
		// - load components
		// - load report status
		// - load hidden status
		$post->load(Post::withComponents());
		// load postreports for hubmanagers
		if ($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$post->load(['reports']);
		}
		// infuencer: check if posts were hidden by you.
		else {
			$post->load([
				'hiddenPosts' => function($query) use($auth_user) {
					$query->where('post_hide.user_id', '=', $auth_user->id)
					->where('post_hide.is_hidden', '=', true);
				},
				'reports' => function($query) use($auth_user) {
					$query->where('post_report.user_id', '=', $auth_user->id);
				}
			]);
		}

		// response
		$data = [
			'data' => [
				'post' => $post->toArray()
			],
			'route' => 'hub::post.view',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/post/{post}/shares
	 * ROUTE hub::post.shares [api.php]
	 *
	 * Get Post shares list
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function getSharesList(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// get shares list
		$shares = PostDispatchQueueItem::query()
			->with(['user', 'attachment.file'])
			->where('hub_id', '=', $hub->id)
			->where('post_id', '=', $post->id)
			->whereIn('result', ['success', 'pending'])
			->orderBy('finished_at', 'DESC')
			->paginate(20);

		// response
		$data = [
			'data' => [
				'shares' => $shares,
			],
			'route' => 'hub::post.shares',
			'success' => true
		];
		return response()->json($data);
	}

	/** 
	* TODO:
	* redirect to javascript route
	* it's causing a ERR_TOO_MANY_REDIRECTS
	* so i might do is redirect this one to the catch_all route to 
	* redirect the page to the index then the SPA will redirect the client back
	* to the original route using javascript
	*/	
	public function getPostRedirect(Request $request, Hub $hub, Post $post)
	{
		return redirect($request->fullUrl());
	}

	/**
	 * POST /api/{hub}/post/{post}
	 * ROUTE hub::post.comment [api.php]
	 *
	 * Post comment
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function comment(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// create comment
		$comment = new Comment();
		$comment->message = $request->message;
		$comment->hub_id = $hub->id;
		$comment->post_id = $post->id;

		// check if auth user is a hub manager
		// then associate the comment to a hub instead of user
		$author = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager')  {
			$author = $hub;
		}
		$comment->hub()->associate($hub);
		$comment->author()->associate($author);
		$comment->save();

		// accrue points for influencer
		if($auth_user->getMembershipTo($hub)->role === 'influencer') {
			$auth_user->membership->accruePoints(PointAccrual::POINTS_POST_COMMENT, 'postcomment', $post);
		}

		// fire event: event.comment.published
		// - only if post author is not the same as the comment author
		if(!$author->is($post->author)) {
			$recipients = collect([$post->author]);
			event('event.comment.published', ['event' => 'event.comment.published', 'comment' => $comment, 'post' => $post, 'actor' => $author, 'hub' => $hub, 'recipients' => $recipients]);
		}

		// response
		$data = [
			'data' => [
				'comment' => $comment
			],
			'route' => 'hub::post.comment', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/post/{post}/instagram/{item}
	 * ROUTE hub::post.instagram
	 * 
	 * get the instagram post details
	 * 
	 * @param \Illuminate\Http\Request   $request
	 * @param \App\Hub                   $hub
	 * @param \App\Post                  $post
	 * @param \App\PostDispatchQueueitem $item
	 * @return \Illuminate\Http\Response
	 */
	public function getInstagramPost(Request $request, Hub $hub, Post $post, PostDispatchQueueItem $item)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// validate item - make sure it has not already been posted
		// this is signified by not having 50 attemtps
		if($item->attempts != 50 && $item->result != 'pending') {
			$data = [
				'message' => 'Item is inactive.',
				'inactive' => true
			];
			return response()->json($data);
		}

		// load related data
		// post
		$post->load(['author']);
		// item
		$item->load(['attachment']);

		// get final content based on base post or overriding modifications
		$content = $post->message;
		if(isset($item->message)) {
			$content = $item->message;
		}
		if(isset($item->params['message'])) {
			$content = $item->params['message'];
		}

		// response
		$data = [
			'queue' => $item,
			'post' => $post,
			'content' => $content,
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/{post}/instagram/{item}
	 * ROUTE hub::post.instagram.sharing
	 * 
	 * attempts to mark the PostDispatchQueueItem to 100.
	 * 
	 * @param \Illuminate\Http\Request   $request
	 * @param \App\Hub                   $hub
	 * @param \App\Post                  $post
	 * @param \App\PostDispatchQueueitem $item
	 * @return \Illuminate\Http\Response
	 */
	public function instagramPostSharing(Request $request, Hub $hub, Post $post, PostDispatchQueueItem $item)
	{

		// set the PostDispatchQueueItem attempts to 100 to start processing by DispatchPosts command's Instagram certainty.
		$item->attempts = 100;
		$item->save();

		// response
		$data = [
			'queue' => $item,
			'post' => $post
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/{post}/like
	 * ROUTE hub::post.like [api.php]
	 *
	 * like/unlike post
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function like(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// get liker
		$liker = $auth_user;
		if($auth_user->getMembershipTo($hub)->role === 'hubmanager') {
			$liker = $hub;
		}

		// get like on content
		$like = Like::query()
			->leftJoin('entity', function ($join) {
					$join->on('like.liker_id', '=', 'entity.entity_id')
						->on('like.liker_type', '=', 'entity.entity_type');
			})
			->where('content_type', '=', get_class($post))
			->where('content_id', '=', $post->id)
			->where('liker_type', '=', get_class($liker))
			->where('liker_id', '=', $liker->id)
			->first();

		// create new like object
		if(is_null($like)) {
			$like = Like::create([
				'is_liked' => false,
			]);
			$like->content()->associate($post);
			$like->liker()->associate($liker);

			// fire event: event.post.liked
			// - only if post author is not the same as the liker
			// $canSend = ($liker->id != $post->author->id) && (get_class($liker) != get_class($post->author)); // just to be sure.
			$canSend = !$liker->is($post->author);
			if($canSend) {
				$recipients = collect([$post->author]);
				event('event.post.liked', ['event' => 'event.post.liked', 'post' => $post, 'actor' => $liker, 'hub' => $hub, 'recipients' => $recipients]);
			}
		}
		$like->is_liked = !$like->is_liked; // reverse like/unlike
		$like->save();

		// accrue or remove points for influencer
		if($auth_user->getMembershipTo($hub)->role === 'influencer') {
			if($like->is_liked) {
				$auth_user->membership->accruePoints(PointAccrual::POINTS_POST_LIKE, 'postlike', $post);
			} else {
				$auth_user->membership->rollbackPoints('postlike', $post);
			}
		}

		// response
		$data = [
			'data' => [
				'like' => $like->toArray(),
			],
			'route' => 'hub::post.like', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/{post}/toggle-hidden
	 * ROUTE hub::post.toggle-hidden [api.php]
	 *
	 * hide/unhide post
	 * todo: implement a influencer middleware for this.
	 * 
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function toggleHidden(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$now = carbon();
		
		// get posthide
		$postHide = PostHide::firstOrCreate([
			'post_id' => $post->id,
			'user_id' => $auth_user->id
		]);

		// toggle the hidden status
		$postHide->is_hidden = !$postHide->is_hidden;
		// set unhid_at and rehid. 
		// may be better if we move this at model observer.
		// PS: dunno if unhid_at and rehid_at is useful here..
		if (!$postHide->is_hidden) {
			$postHide->unhid_at = $now;
		}
		else {
			$postHide->rehid_at = $now;
		}
		$postHide->save();

		// response
		$data = [
			'data' => [
				'post' => $post->toArray(),
				'post_hide' => $postHide->toArray()
			],
			'route' => 'hub::post.toggle-hidden',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/{post}/report
	 * ROUTE hub::post.report [api.php]
	 *
	 * report post
	 * todo: implement a influencer middleware for this.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function report(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// 1. Create report record
		// 2. Create hide record
		// 3. Alter ui to hide post
		// 4. Send notification

		// create the report record
		$report = PostReport::firstOrCreate([
			'post_id' => $post->id,
			'user_id' => $auth_user->id,
			'is_reported' => true
		]);

		// create hide record
		$postHide = PostHide::firstOrCreate([
			'post_id' => $post->id,
			'user_id' => $auth_user->id
		]);
		
		// always hide postHide record when reporting a post.
		$postHide->is_hidden = true;
		$postHide->save();

		// send notification
		event(new PostReported($post, $report));

		// response
		$data = [
			'data' => [
				'post' => $post->toArray(),
				'report' => $report->toArray(),
				'post_hide' => $postHide->toArray(),
			],
			'route' => 'hub::post.report',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/post/{post}/unpublish
	 * ROUTE hub::post.unpublish [api.php]
	 *
	 * unpublish post
	 * todo: implement a hub middleware for this.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Post                 $post
	 * @return \Illuminate\Http\Response
	 */
	public function unpublish(Request $request, Hub $hub, Post $post)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// unpublish post.
		$post->unPublished();

		// response
		$data = [
			'data' => [
				'post' => $post->toArray()
			],
			'route' => 'hub::post.unpublish',
			'success' => true
		];
		return response()->json($data);
	}

}
