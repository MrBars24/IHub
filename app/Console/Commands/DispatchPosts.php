<?php

namespace App\Console\Commands;

// App
use App\Gig;
use App\LinkedAccount;
use App\LinkedinCompanyAccess;
use App\PostDispatchJob;
use App\PostDispatchQueueItem;
use App\Events\Posts\InstagramReady;

// Laravel
use Illuminate\Console\Command;
use Mail;

// 3rd Party
use Codebird\Codebird;
use LinkedIn\LinkedIn as LinkedInApiExchange;

class DispatchPosts extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'posts:dispatch
	                       {--test : Whether we should use test media in this job}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Dispatch posts to social media';

	/**
	 * URL of an image we can use for testing.
	 *
	 * @var string
	 */
	private $test_image = 'http://ihubapp2.staging.bodecontagion.com/uploads/1512222542_BAE2nVxDWj9z.jpg';

	/**
	 * URL of a video we can use for testing.
	 *
	 * @var string
	 */
	private $test_video = 'http://ihubapp2.staging.bodecontagion.com/uploads/1512250665_GwhkjxAd2VOO.mp4';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// handle hanging items first
		$this->handleHangingItems();

		// get current timestamp
		$now = carbon();

		// set attempts count, was 3
		$max_attempts_count = 1;

		// get post dispatch queue items
		// - successfully queued
		// - errored, but less than error tolerance
		$queue = PostDispatchQueueItem::with([
			'post.hub',
			'attachment.file',
			'social' => function($query) {
				$query->whereExists(function($query) {
						$query->selectRaw(1)
							->from('linked_account')
							->where('is_enabled', '=', true)
							->whereRaw('linked_account.native_id = social_entity.parent_id')
							->whereNull('expired_at');
					});
			},
		])
			->where(function($query) use ($now) {
				$query->where('result', '=', 'pending')
					->orWhere(function($query) use ($now) {
						$query->where('result', '=', 'error')
							->where('attempts', '<', 5);
					})
					->orWhere(function($query) use ($now) {
						$query->whereIn('result', ['error', 'failed'])
							->where('platform', '=', 'instagram')
							->where('attempts', '>=', 100) // attempts = 50: on first run of queue item, attempts = 100: after clicking on the open button
							->where('created_at', '<=', $now->copy()->subMinutes(7))
							->where('created_at', '>=', $now->copy()->subHour());
					});
			})
			->get();

		// log job
		$job = new PostDispatchJob();
		$job->started_at = $now;
		$job->result = 'started';
		$job->save();

		// log items in this job (queried above) as started
		foreach($queue as $i => $item) {
			$item->job_id = $job->id;
			$item->result = 'started';
			$item->attempts += 1;
			$item->save();
		}

		// process all items
		foreach($queue as $item) {
			try {
				$post = $item->post;
				$item->started_at = carbon();
				$item->save();

				// check point: make sure post, user, and social account can be identified
				// if not, flag as error and skip item
				if(is_null($post) || is_null($item->social)) {
					$item->finished_at = carbon();
					$item->result = 'error';
					// flag as failed if we've exceeded the max attempts count
					if($item->result == 'error' && $item->attempts >= $max_attempts_count) {
						$item->result = 'failed';
					}
					$item->save();
					continue;
				}

				// set post message
				$post->message = isset($item->message) ? $item->message : '';

				// this function will catch any errors and return 'error'
				$result = null;
				$resultText = null;
				$resultError = null;

				// perform dispatch
				$result = $this->dispatchPostToPlatform($post, $item->social, $item->params, $item, $item->attachment);

				$resultText = $result;
				$resultError = null;
				if($result !== 'success') {
					$resultText = 'error';
					$resultError = $result;
				}
			} catch(\Exception $e) {
				$resultText = 'error';
				$resultError = [
					'code' => $e->getCode(),
					'message' => $e->getMessage(),
				];
				$resultError = json_encode($resultError);

				// expire accounts that match the access token and platform
				$linkedAccounts = LinkedAccount::query()
					->where('token', '=', $item->social->token)
					->where('platform', '=', $item->social->platform)
					->get();
				foreach($linkedAccounts as $account) {
					$account->expire($e->getMessage());
				}
			}

			// round off item
			$item->finished_at = carbon();
			$item->result = $resultText;
			$item->error = $resultError;
			if($item->result == 'error' && $item->attempts >= 3) {
				$item->result = 'failed';
			}
			$item->save();
		}

		// finished hub cycle
		$job->finished_at = date('Y-m-d H:i:s');
		$job->result = 'success';
		$job->save();
	}

	/// Section: Facebook

	private function dispatchPostToFacebook($post, $account, $params = [], $item = null, $attachment = null)
	{
		// prepare message
		$message = $this->getMessage($post, $params);
		$tags = $this->getTagIds($params); // @note: doesn't work without specifying a place - https://developers.facebook.com/docs/graph-api/reference/v2.8/user/feed
		
		$link = null;
		$resource = null;
		$source = null;
		$type = null;
		if(!is_null($attachment)) {
			$link     = $attachment->url;
			$resource = $attachment->resource;
			$type     = $attachment->type;
		} // @todo: resolve attachment sources (link, image, video)

		// case: image
		// https://developers.facebook.com/docs/graph-api/reference/user/photos/
		if($type == 'image') {

			// params
			$postData['caption'] = $message;
			$postData['url'] = $this->getAttachmentSource($attachment);
			$postData['type'] = 'uploaded';

			// end point
			$graph_url = 'https://graph.facebook.com/{id}/photos';
		}
		// case: video
		// https://developers.facebook.com/docs/graph-api/reference/user/videos/
		elseif($type == 'video') {

			// params
			$postData['description'] = $message;
			$postData['file_url'] = $this->getAttachmentSource($attachment);

			// end point
			$graph_url = 'https://graph-video.facebook.com/{id}/videos';
		}
		// other
		else {
			// params
			$postData['message'] = $message;
			$postData['link'] = $link;
			$postData['resource'] = $resource;
			$postData['source'] = $resource;

			if($type == 'youtube' || $type == 'vimeo') {
				$postData['resource'] = '';
				$postData['source'] = '';
			}

			// end point
			$graph_url = 'https://graph.facebook.com/{id}/feed';
		}

		// build request
		$baseData = $postData;
		$url = str_replace('{id}', $account->native_id, $graph_url);

		// data
		$postData = array_merge($baseData, [
			'access_token' => $account->token,
		]);
		// message_tags
		$messageTagsData = [
			'message_tags' => $this->getMessageTags($message,$params)
		];
		// or just do a recursive array_filter, strlen
		$postData = http_build_query(array_merge(array_filter($postData, 'strlen'), $messageTagsData));
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$output = curl_exec($ch);
		curl_close($ch);

		$reply = json_decode($output);
		$replies = [];

		if(!isset($reply->error)) {
			$replies['success'][$account->native_id] = $reply;
		} else {
			$replies['error'][$account->native_id] = $reply;
		}

		// return result
		if(!array_key_exists('error', $replies)) {
			return 'success';
		} else {
			return json_encode(['error' => 'Failed to post to facebook', 'list' => $replies['error']]);
		}
	}

	/// Section: Twitter

	private function dispatchPostToTwitter($post, $account, $params = [], $item = null, $attachment = null)
	{
		// create twitter API exchange
		Codebird::setConsumerKey(config('services.twitter.client_id'), config('services.twitter.client_secret'));
		$cb = Codebird::getInstance();
		$cb->setToken($account->token, $account->secret);

		// prepare message
		$link = null;
		$source = null;
		$media_ids = null;
		if(!is_null($attachment)) {

			// attachment management
			if($attachment->type == 'video') {
				$source = $this->getAttachmentSource($attachment, 'file');
				$media_ids = $this->attachVideo($cb, $source);
			} elseif($attachment->type == 'image') {
				$source = $this->getAttachmentSource($attachment, 'file');
				$media_ids = $this->attachImage($cb, $source);
			} else {
				// link
				$url = $attachment->url;
				$shortened = $attachment->shortened_url;

				$link = $url;

				// apply shortened_url if this is able to be retrieved
				if(!is_null($shortened) && strlen($shortened) > 0) {
					$link = $shortened;
				}

				// check if this link is not already embedded in the post body
				$message = $this->getMessage($post, $params);
				//if(strpos($post->message_original, $url) !== false || strpos($post->message_original, $shortened) !== false) {
				if(strpos($message, $url) !== false || strpos($message, $shortened) !== false) {
					$link = '';
				}
			}
		}

		// social message variable override
		$message = $this->getMessage($post, $params);

		// join message and any links together
		$message = [
			$message,
			$link
		];
		$message = array_filter($message, 'strlen');
		$message = implode(': ', $message);

		// status update
		$data = [];
		$data['status'] = $message;
		if(strlen($media_ids)) {
			$data['media_ids'] = $media_ids;
			$this->info('media ids: ' . $media_ids);
		}
		$reply = $cb->statuses_update($data);

		// return result
		if(!isset($reply->errors)) {

			// send debug email to ensure no false positives
			Mail::raw(json_encode($reply), function($message) {

				// compile message
				$message
					->from('noreply@influencerhub.com', 'Influencer HUB')
					->to('satoshi@bodecontagion.com', 'Satoshi Payne')
					->subject('Influencer HUB Twitter Response');
			});

			return 'success';
		} else {
			return json_encode($reply->errors);
		}
	}

	private function attachImage($cb, $source)
	{
		// will hold the uploaded IDs
		$media_ids = [];

		// upload all media files
		$reply = $cb->media_upload([
			'media' => $source
		]);

		// and collect their IDs
		try {
			$media_ids[] = $reply->media_id_string;
		} catch(\Exception $e) {
			if(isset($reply->errors) && !empty($reply->errors)) {
				throw new \Exception($reply->errors[0]->message);
			}
		}

		// convert media ids to string list
		$media_ids = implode(',', $media_ids);
		return $media_ids;
	}

	private function attachVideo($cb, $source)
	{
		// video details
		$this->info('source: ' . $source);

		$filesize = $remainingSize = strlen(file_get_contents($source));
		$this->info('filesize: ' . $filesize);

		$handle = fopen($source, 'rb');
		$chunk_size = 1024 * 1024 * 0.5; // observe fread limit
		$i = 0;

		// INIT

		// note: chunked uploads makes the distinction between async and sync uploads. Both have different capacities / limits.
		// see: https://dev.twitter.com/rest/media/uploading-media.html
		$reply = $cb->media_upload([
			'command'        => 'INIT',
			'media_type'     => 'video/mp4',
			'total_bytes'    => $filesize,
			'media_category' => 'tweet_video' // media_category added to flag upload as 'async' https://twittercommunity.com/t/media-category-values/64781/6
		]);

		try {
			$media_id = $reply->media_id_string;
		} catch(\Exception $e) {
			if(isset($reply->errors) && !empty($reply->errors)) {
				throw new \Exception($reply->errors[0]->message);
			}
		}

		$this->info('INIT ' . $media_id);
		$this->info($media_id);

		// APPEND

		$missedSegments = [];
		$totalSize = 0;
		if($handle === false) {
			$this->info('cannot open');
			return false;
		}
		while(!feof($handle)) {
			$buffer = fread($handle, $chunk_size);

			$remainingSize -= $chunk_size;

			$totalSize += strlen($buffer);

			$reply = $cb->media_upload([
				'command'       => 'APPEND',
				'media_id'      => $media_id,
				'segment_index' => $i,
				'media'         => $buffer
			]);

			$this->info('APPEND ' . strlen($buffer) . ' / ' . $totalSize . ' / ' . $chunk_size . ' / ' . $i++);
			$this->info(json_encode($reply));

			if($reply->httpstatus != 204 && $reply->httpstatus != 200) {
				$missedSegments[] = [
					'at_index' => $i - 1,
					'at_size' => $totalSize,
				];
			}
		}
		fclose($handle);

		// FINALIZE

		$reply = $cb->media_upload([
			'command'  => 'FINALIZE',
			'media_id' => $media_id
		]);

		$this->info('FINALIZE ' . $media_id);
		$this->info(json_encode($reply));
		$this->info('final stats');
		$this->info('file size: ' . $filesize . ', total size: ' . $totalSize . ', segments: ' . $i);
		vd($missedSegments);

		// check if "processing_info" is present
		if(!isset($reply->processing_info)) {
			return false;
		}
		// set how many seconds to check before allowing the status update to occur
		$checkAfterSecs = $reply->processing_info->check_after_secs;

		// STATUS

		$stoppage = 0;
		$ready = false;
		while(!$ready && $stoppage < 6 && $stoppage !== false) {
			sleep($checkAfterSecs); // sleep is used to allow the video time to process: http://stackoverflow.com/questions/32231642/uploading-videos-to-twitter-using-api
			$status = $cb->media_upload([
				// note: must use GET, or else will fail with "media parameter is missing.": https://twittercommunity.com/t/status-update-with-an-uploaded-video/57094/12
				'httpmethod' => 'GET',
				'command'  => 'STATUS',
				'media_id' => $media_id
			]);
			vd($status);
			if(!isset($status->processing_info)) {
				$stoppage = false;
			}
			$checkAfterSecs = isset($status->processing_info->check_after_secs) ? $status->processing_info->check_after_secs : 0;
			$stoppage++;
			if($stoppage == 6 || $checkAfterSecs == 0) {
				$ready = true;
			}
		}

		// convert media ids to string list
		$media_ids = implode(',', [$media_id]);
		return $media_ids;
	}

	/// Section: LinkedIn

	private function dispatchPostToLinkedIn($post, $account, $params = [], $item = null, $attachment = null)
	{
		$attachmentData = [];

		// prepare message
		$link = null;
		if(!is_null($post->attachment)) {

			// media uploads
			if($attachment->type !== 'link') {

				// @todo: let's add fields here to specify title/description for image/video attachments
				$title = '';
				$description = '';
				$link = $attachment->url;
				$source = '';

				// if image or video
				if($attachment->type == 'image' || $attachment->type == 'video') {
					$source = $this->getAttachmentSource($attachment);
					$link = $source;
					$source = '';
				}
			}
			// links
			else {
				$title = $attachment->title;
				$description = $attachment->description;
				$link = $attachment->url;
				$source = $attachment->resource;
			}

			// build 'content'
			$attachmentData = [
				'title' => $title, 
				'description' => $description,
				'submitted-url' => $link,
				'submitted-image-url' => $source,
			];

			// unset the submitted-image-url if the post attachment is an image or video type
			if($attachment->type == 'image' || $attachment->type == 'video') {
				unset($attachmentData['submitted-image-url']);
				unset($attachmentData['description']);
				//$attachmentData['title'] = ucfirst($attachment->type); // add this to avoid displaying the image url underneath image (it will show the text "Image" instead, no way around this)
				
				// url domain substitution
				// or just $post->hub->sharing_meta_linkedin;
				$attachmentData['title'] = !is_null($post->hub->sharing_meta_linkedin) ? $post->hub->sharing_meta_linkedin : ucfirst($attachment->type);
				$attachmentData['submitted-url'] = str_replace(url('/'), env('LINKEDIN_CUSTOM_DOMAIN', 'https://ihubapp2.staging.bodecontagion.com'), $attachmentData['submitted-url']);
			}

			$attachmentData = array_filter($attachmentData, 'strlen');
		}

		// prepare message
		$message = $this->getMessage($post, $params);

		// is this a profile or company post?
		// linkedin company
		if($account->entity_type == LinkedinCompanyAccess::class) {
			$url = "/companies/{$account->native_id}/shares";
			$headers = [];
		}
		// linkedin profile
		else {
			$url = '/people/~/shares?format=json';
			$headers = [
				'Authorization: Bearer ' . $account->token
			];
		}

		// create linkedin exchange
		$settings = [
			'api_key' => config('services.linkedin.client_id'),
			'api_secret' => config('services.linkedin.client_secret'),
			'callback_url' => config('services.linkedin.redirect'),
		];
		$postData = [
			'comment' => $message,
			'visibility' => [
				'code' => 'anyone'
			]
		];
		// only add 'content' if attachment array is not empty
		if(!empty($attachmentData)) {
			$postData['content'] = $attachmentData;
		}

		// display debug info
		$this->info('post info:');
		$this->info('$url');
		vd($url);
		$this->info('$postData');
		vd($postData);
		$this->info('$headers');
		vd($headers);

		$linkedin = new LinkedInApiExchange($settings);
		$linkedin
			->setAccessToken($account->token)
			->setState($account->native_id);
		$reply = $linkedin->fetch($url, $postData, LinkedInApiExchange::HTTP_METHOD_POST, $headers);

		// pretty crude; linkedin doesn't explicitly send back an "error"
		if(isset($reply['updateKey'])) {
			return 'success';
		} else {
			return json_encode($reply);
		}
	}

	/// Section: Pinterest

	private function dispatchPostToPinterest($post, $account, $params = [], $item = null, $attachment = null)
	{
		$boards = isset($params['boards']) ? $params['boards'] : '';
		$boards = array_filter(explode(',', $boards));

		// post data
		$data = [
			'access_token' => $account->token,
			'note' => $this->getMessage($post, $params),
		];

		// check point
		if(!is_null($attachment) && !is_null($attachment->resource) && $attachment->type == 'image') {
			$data += [
				'image_url' => $this->getAttachmentSource($attachment),
			];
		}
		// no image found; return
		else {
			return json_encode(['error' => 'Image not found; aborting']);
		}

		$data['board'] = $account->native_id;

		// data
		$postData = http_build_query(array_filter($data, 'strlen'));
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.pinterest.com/v1/pins/');
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$reply = curl_exec($ch);
		curl_close($ch);

		$reply = json_decode($reply);

		// pretty crude; pinterest doesn't explicitly send back an "error", so we'll just check if the response has the 'data' attribute
		$replies = [];
		if(isset($reply->data) && isset($reply->data->url)) {
			$replies['success'][$account->native_id] = $reply;
		} else {
			$replies['error'][$account->native_id] = $reply;
		}

		// return result
		if(!array_key_exists('error', $replies)) {
			return 'success';
		} else {
			return json_encode(['error' => 'Failed to post to pinterest', 'list' => $replies['error']]);
		}
	}

	/// Section: YouTube

	private function dispatchPostToYouTube($post, $account, $params = [], $item = null, $attachment = null)
	{
		// params
		$title    = isset($params['youtube_title']) ? $params['youtube_title'] : '(Video from Influencer HUB)';
		$category = isset($params['youtube_category']) ? $params['youtube_category'] : '';

		// check point
		if(!is_null($attachment) && !is_null($attachment->resource) && $attachment->type == 'video') {
			$path = $this->getAttachmentSource($attachment, 'file');
		}
		// no video found; return
		else {
			return json_encode(['error' => 'Video not found; aborting']);
		}

		$data = [
			'title' => $title,
			'description' => $this->getMessage($post, $params),
			'category_id' => $category,
			'tags' => [
				'influencerhub',
				'publish',
			]
		];

		// the scopes required for the video post
		$scopes = [
			'https://www.googleapis.com/auth/youtube',
			'https://www.googleapis.com/auth/youtube.upload',
			'https://www.googleapis.com/auth/youtube.readonly'
		];

		// create client
		$client = new \Google_Client;
		$client->setApplicationName('Influencer HUB');
		$client->setClientId(config('services.youtube.client_id'));
		$client->setClientSecret(config('services.youtube.client_secret'));
		$client->setScopes($scopes);
		$client->setAccessType('offline');
		$client->setClassConfig('Google_Http_Request', 'disable_gzip', true);
		$client->setAccessToken($account->token);

		// youtube
		$youtube = new \Google_Service_YouTube($client);

		// refresh
		$token = $this->handleYouTubeAccessToken($account, $client);
		if($token === false) {
			return json_encode(['error' => 'Access token not found; aborting']);
		}

		// upload process

		// setup snippet
		$snippet = new \Google_Service_YouTube_VideoSnippet();
		if(array_key_exists('title', $data)) {
			$snippet->setTitle($data['title']);
		}
		if(array_key_exists('description', $data)) {
			$snippet->setDescription($data['description']);
		}
		if(array_key_exists('tags', $data)) {
			$snippet->setTags($data['tags']);
		}
		if(array_key_exists('category_id', $data)) {
			$snippet->setCategoryId($data['category_id']);
		}

		// set privacy
		$status = new \Google_Service_YouTube_VideoStatus();
		$status->privacyStatus = 'public';

		// set snippet and status
		$video = new \Google_Service_YouTube_Video();
		$video->setSnippet($snippet);
		$video->setStatus($status);

		// set chunk size
		$chunkSize = 1 * 1024 * 1024;

		// not sure what this does but..
		$client->setDefer(true);

		// build insert request
		$insert = $youtube->videos->insert('status,snippet', $video);

		// media file upload
		$media = new \Google_Http_MediaFileUpload(
			$client,
			$insert,
			'video/*',
			null,
			true,
			$chunkSize
		);

		// file size
		$filesize = strlen(file_get_contents($path));
		$media->setFileSize($filesize);

		// upload file in chunks
		$status = false;
		$handle = fopen($path, "rb");

		while(!$status && !feof($handle)) {
			$chunk = fread($handle, $chunkSize);
			$status = $media->nextChunk($chunk);
		}
		fclose($handle);

		// not sure what this does but..
		$client->setDefer(true);

		// return result
		if(isset($status['id'])) {
			return 'success';
		} else {
			return json_encode(['error' => 'Failed to post to youtube', 'list' => '']);
		}
	}

	private function handleYouTubeAccessToken($account, $client)
	{
		$accessToken = $client->getAccessToken();

		// no access token?
		if(is_null($accessToken)) {
			return false;
		}

		// detect expired token
		if($client->isAccessTokenExpired()) {
			$accessToken = json_decode($accessToken);
			$refreshToken = $accessToken->refresh_token;
			$client->refreshToken($refreshToken);
			$newAccessToken = $client->getAccessToken();

			// get source data account
			$source = LinkedAccount::query()
				->where('platform', '=', 'youtube')
				->find($account->entity_id);

			// save to database
			$source->token = $newAccessToken;
			$source->save();

			return $newAccessToken;
		}
		return true;
	}

	/// Section: Instagram

	private function dispatchPostToInstagram($post, $account, $params = [], $item = null, $attachment = null)
	{
		// on first attempt, mark as error with 50 attempts, this will prevent it from executing again
		if($item->attempts == 1) {
			$item->result = 'error';
			$item->attempts = 50;
			$item->save();

			// send notification
			// - item = PostDispatchQueueItem instance
			// - post = Post instance
			// - recipient = post sharer (\App\User|\App\Hub instance)

			// get recipient of the notification
			$recipient = $item->user;
			if($recipient->getMembershipTo($post->hub)->role == 'hubmanager') {
				$recipient = $post->hub;
			}
			event(new InstagramReady($item, $post, $recipient));

			return 'error';
		}
		// when notification has been accessed
		else {
			// query for post created using instagram API
			$url = 'https://api.instagram.com/v1/users/self/media/recent';
			$token = $account->token;

			$postData = [
				'access_token' => $token
			];
			$postData = http_build_query(array_filter($postData, 'strlen'));
			$ch = curl_init();

			$this->info($postData);
			$this->info($url . $postData);

			$this->info('----');

			curl_setopt($ch, CURLOPT_URL, $url . '?' . $postData);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			$output = curl_exec($ch);
			curl_close($ch);

			$reply = json_decode($output);
			$certainty = 0;
			foreach($reply->data as $igPost) {
				$certainty = max($certainty, $this->detectInstagramPostSimilarities($post, $item, $igPost, $params));
			}
			$item->certainty = $certainty;
			$item->save();

			return 'success';
		}
	}

	private function detectInstagramPostSimilarities($post, $item, $igPost, $params)
	{
		// look in: obj->caption->text, obj->created_time
		$this->info('calculate instagram post certainty');

		// check time stamps
		$time = $igPost->created_time;
		$created_time = new \DateTime();
		$created_time->setTimestamp($time);
		$created_time = $created_time->format('Y-m-d H:i:s');
		$created_at = $post->created_at->format('Y-m-d H:i:s');

		// ig post must be created after the queued item
		if($created_at > $created_time) {
			return 0;
		}

		// compare messages: directly
		$message = $this->getMessage($post, $params);
		$attempts = $item->result != 'pending' ? ($item->attempts - 100) : 0;
		if($message == $igPost->caption->text) {
			$certainty = 97 - ($attempts * 2);

			// show certainty result
			$this->info('exact match found');
			$this->info('certainty: ' . $certainty);
			return $certainty;
		}

		// compare messages: by parts
		$parts1 = explode(' ', $message);
		$parts2 = explode(' ', $igPost->caption->text);
		$partsX = array_intersect($parts1, $parts2);

		$hasParts = count($partsX) / count($parts1);

		if($hasParts > 0) {
			$hasCountDiff = abs(count($parts1) - count($partsX));
			switch($hasCountDiff) {
				case 0:
					$hasCountDiff = 0;
					break;
				case 1:
					$hasCountDiff = 10;
					break;
				case 2:
					$hasCountDiff = 25;
					break;
				case 3:
					$hasCountDiff = 45;
					break;
				case 4:
					$hasCountDiff = 55;
					break;
				case 5:
					$hasCountDiff = 65;
					break;
			}
			$certainty = max(80 - $hasCountDiff - ($attempts * 2), 0);

			// show certainty result
			$this->info('partial match found');
			$this->info('certainty: ' . $certainty);
			return $certainty;
		}

		// doesn't seem to be any intersecting parts
		$this->info('no match found');
		$this->info('certainty: ' . 0);
		return 0;
	}

	/// Section: Helpers

	/**
	 * Handle hanging job items (items that are not getting processed properly or have errored)
	 * This will notify developers that there is an issue in the post dispatch job
	 */
	private function handleHangingItems()
	{
		// get current timestamp
		$now = carbon();

		// get hanging post dispatch queue items
		// - have not been notified
		// - created more than 2 hours ago
		// - started but not finished
		$hanging = PostDispatchQueueItem::query()
			->where('hanging_notified', '=', false)
			->where('result', '=', 'started')
			->where('created_at', '<', $now->copy()->subHours(2))
			->get();

		// send notification if hanging items are identified
		if($hanging->count() > 0) {
			// report content
			$output = 'There are ' . $hanging->count() . ' items hanging, please troubleshoot.';

			// notify developer of hanging items
			foreach($hanging as $i => $item) {
				$item->hanging_notified = true;
				$item->save();
			}

			// send mail
			Mail::raw($output, function($message) {
				$message
					->from('noreply@influencerhub.com', 'Influencer HUB')
					->to('satoshi@bodecontagion.com', 'Satoshi Payne')
					->subject('Influencer HUB Post Dispatch Hanging Job Items');
			});
		}
	}

	private function dispatchPostToPlatform($post, $account, $params, $item, $attachment)
	{
		// implement: facebook, twitter, etc....
		switch($account->platform) {
			case 'facebook':
				$result = $this->dispatchPostToFacebook($post, $account, $params, $item, $attachment);
				break;
			case 'twitter':
				$result = $this->dispatchPostToTwitter($post, $account, $params, $item, $attachment);
				break;
			case 'linkedin':
				$result = $this->dispatchPostToLinkedIn($post, $account, $params, $item, $attachment);
				break;
			case 'pinterest':
				$result = $this->dispatchPostToPinterest($post, $account, $params, $item, $attachment);
				break;
			case 'youtube':
				$result = $this->dispatchPostToYouTube($post, $account, $params, $item, $attachment);
				break;
			case 'instagram':
				$result = $this->dispatchPostToInstagram($post, $account, $params, $item, $attachment);
				break;
			default: // other
				$result = 'success';
				break;
		}
		return $result;
	}

	/**
	 * sanitized message
	 */
	private function getMessage($post, $params)
	{
		// new: message_store[, tags]
		//$params = (array) $params;
		if(isset($params['message'])) {
			$message = $params['message'];

			$message_store = isset($params['message_store']) ? $params['message_store'] : [];
			
			// replace all tagged accounts by its name,
			// depreciation notice: this function is kept just because the old tagged users were stored using its native_id
			if (!empty($message_store)) {
				preg_match_all('/\[\@\(([0-9])+\)\]/', $message, $matches);
				if(!empty($matches)) {
					$texts   = $matches[0];
					$indices = $matches[1];
					foreach($indices as $i => $index) {
						// determine the name displayed in platform
						$tagged = $message_store[$i];
						if (isset($tagged['name'])) {
							$name = $tagged['name'];
						}
						else {
							$name = $tagged['platform'] == 'facebook' ? $tagged['display_name'] : '@'.$tagged['screen_name'];
						}

						$old = $texts[$i];
						$new = $name;
						$message = str_replace($old, $new, $message);
					}
				}
			}
			
			// perform replacements
			$message = str_replace('<p><br /><\p>', '<p><\p>', $message);
			$message = str_replace('<p><br><\p>', '<p><\p>', $message);
			$message = str_replace('<br />', "\n", $message);
			$message = str_replace('<br>', "\n", $message);
			$message = str_replace('</p><p>', "\n", $message);
			$message = str_replace('<p>', '', $message);
			$message = str_replace('</p>', '', $message);
			$message = str_replace('&nbsp;', ' ', $message);
			$message = trim($message);
		}
		// old: message || message_original
		else {
			$message = $post->message_original;
		}
		return $message;
	}

	private function getTagIds($params)
	{
		$tags = isset($params['tags']) ? $params['tags'] : [];
		$list = [];
		foreach($tags as $tag) {
			$list[] = $tag['id'];
		}
		return $list;
	}

	/**
	 * generate message_tags structure.
	 * current platform post that supports tagging: facebook, twitter, instagram 
	 * but for future enhancements, we'll keep tagged users function here.
	 * 
	 * @param string $message     the sanitized message returned by @getMessage
	 * @param object $params
	 * @return array
	 */
	private function getMessageTags($message, $params)
	{
		$message_store = isset($params['message_store']) ? $params['message_store'] : [];
		$list = [];
		if (!empty($message_store)) {
			// build facebook message_tags param
			if ($params['platform'] == 'facebook') {
				foreach($message_store as $key => $tagged) {
					$name = $tagged['name'];
					$tag = [
						'id' => $tagged['native_id'],
						'name' => $name,
						'type' => $tagged['type'],
						'offset' => strpos($message, $name),
						'length' => strlen($name)
					];
					array_push($list, $tag);
				}
			}
		}
		return $list;
	}

	/**
	 * Localhost environments aren't supported well for media that require specifying web paths
	 * This method detects whether the command is run under test conditions
	 *
	 * @param  \App\PostAttachment $attachment
	 * @param  string              $mode       Either 'web' or 'file'
	 * @return string
	 */
	private function getAttachmentSource($attachment, $mode = 'web')
	{
		// detect test run
		$test = $this->option('test');

		// if we're just getting the physical file, then just return that
		if($mode == 'file') {
			if($attachment->type == 'image' || $attachment->type == 'video') {
				$media = $attachment->file->getFilePath();
			} else {
				$media = null;
			}
		}
		// test run
		elseif($test) {
			if($attachment->type == 'image') {
				$media = $this->test_image;
			} elseif($attachment->type == 'video') {
				$media = $this->test_video;
			} else {
				$media = null;
			}
		}
		// use the real media associated with this attachment
		else {
			$media = $attachment->file->getWebPath();
		}
		return $media;
	}
}