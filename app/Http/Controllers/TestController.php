<?php

namespace App\Http\Controllers;

// App
use App\FileStorage;
use App\Gig;
use App\LinkedAccount;
use App\PostAttachment;
use App\User;
use App\PostDispatchQueueItem;
use App\PushNotificationQueueItem;
use App\Modules\Files\FileManager;
use App\Events\Social\AccountConnected;
use App\Events\Notification\SendPush;
use App\Modules\Notifications\PushBots;

// Laravel
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

// 3rd Party
use Image;
use Cookie;
use LinkedIn\LinkedIn as LinkedInApiExchange;
use Embed\Embed;
use Embed\Exceptions\InvalidUrlException;
use LinkedIn\AccessToken;
use LinkedIn\Client;
use OneSignal;

class TestController extends Controller
{
	/// Section: Test Cases

	/**
	 * Test cleanup category settings
	 *
	 * @param Request $request
	 */
	/*public function cleanupSettings(Request $request)
	{
		$memberships = \App\Membership::all();

		foreach($memberships as $membership) {
			// delete category settings
			\DB::statement("delete
				from alert_category_setting
				where membership_id = {$membership->id}
				and (
				    category_id not in (
				        select id from category
				    )
				    or
				    category_id not in (
				        select category.id
				        from category
				        left join membership on category.hub_id = membership.hub_id
				        where membership.id = {$membership->id}
				    )
				)");
			echo '<br />Deleting category settings from ' . $membership->id;
		}
	}*/

	public function environment()
	{
		echo(app()->environment());
	}

	/**
	 * Test the Sharing API of Facebook - https://developers.facebook.com/docs/sharing/web
	 *
	 * @param Request $request
	 */
	private function facebookShare(Request $request)
	{
		$url = 'https://www.linkedin.com/pulse/i-love-my-newly-adopted-country-singapore-all-reasons-anton-kreil/';
		return view('test.share', ['url' => $url]);
	}

	public function instagramCertainty()
	{
		// query for post created using instagram API
		$url = 'https://api.instagram.com/v1/users/self/media/recent';
		$token = '2029120453.16a70f1.e16058b68cb3422280bd8cb1d5ba4a0e';

		$postData = [
			'access_token' => $token
		];
		$postData = http_build_query(array_filter($postData, 'strlen'));
		$ch = curl_init();

		vd($postData);
		vd($url . $postData);

		vd('----');

		curl_setopt($ch, CURLOPT_URL, $url . '?' . $postData);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$output = curl_exec($ch);
		curl_close($ch);

		$reply = json_decode($output);
		vd($reply);
	}

	/**
	 * Test the expire account logic
	 *
	 * @param Request $request
	 */
	public function expireTest(Request $request)
	{
		// list of error messages to test
		$errorMessages = [
			'Error validating access token: Session has expired on Sunday, 13-May-18 23:03:05 PDT. The current time is Tuesday, 19-Jun-18 23:36:02 PDT.',
			'Error validating access token: Session has expired on Sunday, 13-May-18 23:03:05 PDT. The current time is Wednesday, 23-May-18 21:52:02 PDT.',
			'Error validating access token: Session has expired on Sunday, 13-May-18 23:03:05 PDT. The current time is Wednesday, 27-Jun-18 18:52:01 PDT.',
			'The token used in the request has expired.',
			'Invalid or expired token.',
			'Error validating access token: The session has been invalidated because the user changed their password or Facebook has changed the session for security reasons.',
			'Then token used in this request has been revoked by the user.',
			'Request Error: Then token used in this request has been revoked by the user.. Raw Response: Array\n(\n    [errorCode] => 0\n    [message] => Then token used in this request has been revoked by the user.\n    [requestId] => KZ9CWOY6A9\n    [status] => 401\n    [timestamp] => 1523579882656\n)\n'
		];

		foreach($errorMessages as $message) {
			// logic taken from LinkedAccount.php
			if(
				// condition 1
				(strpos(strtolower($message), 'access') !== false || strpos(strtolower($message), 'request') !== false || strpos(strtolower($message), 'expired token') !== false) &&
				// condition 2
				strpos(strtolower($message), 'token') !== false &&
				// condition 3
				(strpos(strtolower($message), 'expired') !== false || strpos(strtolower($message), 'invalid') !== false || strpos(strtolower($message), 'unable to verify') !== false || strpos(strtolower($message), 'error validating') !== false || strpos(strtolower($message), 'revoked by the user') !== false)
			) {
				echo($message . ': <span style="color:green">flagged</span>' . "<br />\n");
			} else {
				echo($message . ': <span style="color:red">not flagged</span>' . "<br />\n");
			}
		}
	}

	/**
	 * Test URL cURL
	 *
	 * @param Request $request
	 */
	public function curlUrl(Request $request)
	{
		// send request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request->input('url'));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		var_dump($output);
	}

	/**
	 * fire InstagramReady event
	 */
	public function fireInstagramReady()
	{
		$item = PostDispatchQueueItem::orderBy('created_at', 'DESC')->take(1)->first();
		$post = $item->post;
		event(new \App\Events\Posts\InstagramReady($item, $post));
	}

	/**
	 * check APP_URL in environment variable
	 */
	public function checkAppUrl(Request $request)
	{
		vd(env('APP_URL'));
		vd(route('general::account.loggedin'));
		vd(url('/account-loggedin'));
	}

	public function checkCommandRunner()
	{
		vd(env('COMMAND_RUNNER_PASSWORD'));
	} 

	/**
	 * redirect to account-loggedin
	 */
	public function redirectAccountLoggedin(Request $request)
	{
		return redirect(url('/account-loggedin'));
	}

	public function handleLinks(Request $request)
	{
		$url = url('/') . '/bodecontagion/newsfeed';
		return redirect(route('general::handle-link', ['url' => $url]));
	}

	/**
	 * Test URL scraping
	 *
	 * @param Request $request
	 */
	public function scrapeUrl(Request $request)
	{
		$info = Embed::create($request->input('url'));
		var_dump($info);
	}

	/**
	 * Test linkedin API for companies
	 *
	 * @param Request $request
	 */
	public function linkedinCompanies(Request $request)
	{
		$account = LinkedAccount::query()
			->where('platform', '=', 'linkedin')
			->where('user_id', '=', 5)
			->first();

		vd($account->toArray());

		// #1: sharing via profile

		// end point details
		/*$url = '/people/~/shares?format=json';
		$postData = [
			'comment' => 'Test message',
			'visibility' => [
				'code' => 'anyone'
			],
			'content' => [
				'title' => 'Test title',
				'description' => 'Test description',
				'submitted-url' => 'http://google.com',
				'submitted-image-url' => 'https://www.google.com.au/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png',
			]
		];
		$method = LinkedInApiExchange::HTTP_METHOD_POST;
		$headers = [
			'Authorization: Bearer ' . $account->token
		];*/

		// #2: companies
		/*$url = '/companies:(id,name,description,square-logo-url)';
		$postData = [
			'is-company-admin' => 'true'
		];
		$method = LinkedInApiExchange::HTTP_METHOD_GET;
		$headers = [];*/

		// #3: sharing via company

		// end point details
		$id = '6596556';
		$url = "/companies/{$id}/shares";
		$postData = [
			'comment' => 'Test message',
			'visibility' => [
				'code' => 'anyone'
			],
			'content' => [
				'title' => 'Test title',
				'description' => 'Test description',
				'submitted-url' => 'http://google.com',
				'submitted-image-url' => 'https://www.google.com.au/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png',
			]
		];
		$method = LinkedInApiExchange::HTTP_METHOD_POST;
		$headers = [
			//'Authorization: Bearer ' . $account->token
		];

		// linkedin api
		$settings = [
			'api_key' => config('services.linkedin.client_id'),
			'api_secret' => config('services.linkedin.client_secret'),
			'callback_url' => config('services.linkedin.redirect'),
		];
		$linkedin = new LinkedInApiExchange($settings);
		$linkedin
			->setAccessToken($account->token)
			->setState($account->native_id);
		$reply = $linkedin->fetch($url, $postData, $method, $headers);

		dd($reply);

		// the following work:
		// https://api.linkedin.com/v1/companies?format=json&is-company-admin=true&oauth2_access_token=AQX8r-NacQAc37-MacuNkn3zZ11jJQQWVnBOqt36h8S8P68MVqejYRfMBR89Z7kvAGzf86daDx76y4uB-MJ3f0BrFZQ7qbdKnTEPDTU6FSQZXsoSmhPmPuIfJSppxuPYCQFdYePd1weHF76GI-F5tbStZgwsMLwP9QvKn4xyj5j3SKeCmIAIfy2xiLhLjfWWoQvLDLj3TlbN7D-SghOegqa3-xxgtwNPopTHZG_qr0HnlcVAxLl8Os7bu2c-2n4wIeftSZ8B0UDU9B3C7G4S1jN0TmWgTiAoBMcXDtNw7XKr-Dhi3vzmkK3pk9Fbs3Y0wDt6IKbkZQ6gPAHnPwd_2pgHaHE-ug

		// resources:
		// https://developer.linkedin.com/docs/company-pages#list_companies - get a list of companies under a profile
		// https://developer.linkedin.com/docs/fields/company-profile - company profile URL
	}

	/**
	 * Test linkedin Rich Media API
	 *
	 * @param Request $request
	 */
	public function linkedinRichMedia(Request $request)
	{
		$account = LinkedAccount::query()
			->where('platform', '=', 'linkedin')
			->where('user_id', '=', 5)
			->first();

		// sample image url
		$filename = "http://www.google.com.au/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png";
		$file = array(
			"name" => 'resource',
			"filename" => "sample_image.png",
			"content" => file_get_contents($filename)
		);

		// build post fields
		$boundary = uniqid();
		$delimiter = '-------------' . $boundary;
		$post_data = $this->build_data_files($boundary, $file);

		vd($account->toArray());
		$curl = curl_init();

		// curl request
		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://api.linkedin.com/media/upload",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $post_data,
			CURLOPT_HTTPHEADER => array(
				"authorization: Bearer " . $account->token,
				"Content-Type: multipart/form-data; boundary=" . $delimiter,
				"Content-Length: " . strlen($post_data)
			),
		));

		//response
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if($err) {
			echo "cURL Error #:" . $err;
		}else{
			echo $response;
		}
	}

	/**
	 * Test zoonman Linkedin library
	 *
	 * @param Request $request
	 * @return void
	 */
	public function zoonmanLinkedin(Request $request)
	{
		$account = LinkedAccount::query()
			->where('platform', '=', 'linkedin')
			->where('user_id', '=', 5)
			->first();

		// instantiate the Linkedin client
		$client = new Client(
			env('LINKEDIN_APP_ID', '753wdedrlfurd4'),
			env('LINKEDIN_APP_SECRET', 'ZQcZf4HmF3XYEjq1')
		);

		// set token for client
		$accessToken = new AccessToken($account->token, $account->expired_at);
		$client->setAccessToken($accessToken);

		#1: sharing via profile
		$postData = [
			'comment' => 'Test message',
			'visibility' => [
				'code' => 'anyone'
			],
			'content' => [
				'title' => 'Test title',
				'description' => 'Test description',
				'submitted-url' => 'http://google.com',
				'submitted-image-url' => 'https://www.google.com.au/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png',
			]
		];

		// request
		$reply = $client->post(
			'people/~/shares?format=json',
			$postData
		);

		// #2: Get Company page profile
		// $companyId = '6596556'; // use id of the company where you are an admin
		// $reply = $client->get('companies/' . $companyId . ':(id,name,num-followers,description)');

		// #3: sharing via company
		// $companyId = '6596556';
		// $url = "companies/{$companyId}/shares";
		// $postData = [
		// 	'comment' => 'Checkout this amazing PHP SDK for LinkedIn!',
		// 	'content' => [
		// 		'title' => 'Influencer PHP Client for LinkedIn API',
		// 		'description' => 'OAuth 2 flow, composer Package',
		// 		'submitted-url' => 'https://github.com/zoonman/linkedin-api-php-client',
		// 		'submitted-image-url' => 'https://github.com/fluidicon.png',
		// 	],
		// 	'visibility' => [
		// 		'code' => 'anyone'
		// 	]
		// ];

		// $reply = $client->post($url, $postData);

		// $filename = 'http://www.google.com.au/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png';
		// $client->setApiRoot('https://api.linkedin.com/');
		// $reply = $client->upload($filename);

		dd($reply);
	}

	/**
	 * Test carbon date arithmetic
	 *
	 * @param Request $request
	 */
	public function dateMath(Request $request)
	{
		vd(carbon());

		$commence_at_minute_x = carbon()->minute;
		$commence_at_minute_y = ceil($commence_at_minute_x / 15) * 15;
		$commence_at = carbon()->addMinutes($commence_at_minute_y - $commence_at_minute_x)->second(0);
		dd($commence_at);
	}

	/**
	 * Test html2text functionality
	 *
	 * @param Request $request
	 */
	public function html2text(Request $request)
	{
		$string = 'Writer seeks links to  <a href="https://twitter.com/hashtag/vegan?src=hash" dir="ltr" ><s>#</s><b>vegan</b></a> travel guides for directory  <a href="https://www.sourcebottle.com/query.asp?ref=emailalert&amp;iid=8&amp;qid=71778" dir="ltr" ><span class="tco-ellipsis"></span><span class="js-display-url">sourcebottle.com/query.asp?ref=</span><span class="tco-ellipsis">…</span></a>  <a href="https://twitter.com/hashtag/vegantravel?src=hash" dir="ltr" ><s>#</s><b>vegantravel</b></a>';

		echo(htmlspecialchars($string));

		echo('<hr />');

		die(convert_html_to_text($string));
	}

	/**
	 * Test social connect functionality
	 *
	 * @param Request $request
	 */
	public function socialConnect(Request $request)
	{
		//$auth_user = auth()->user();
		$auth_user = session_user();

		// check point: auth user
		if(is_null($auth_user) && !$request->input('id')) {
			die('need to be logged in to access this test, please specify an "id" with this request');
		}

		if(is_null($auth_user)) {
			$user = User::where('slug', '=', $request->input('id'))->first();

			if(is_null($user)) {
				die('user not found');
			}

			auth()->login($user);
			$auth_user = auth()->user();
		}

		// output
		die(
			'<p>User: ' . $auth_user->name . ' (' . $auth_user->email . ')' . '</p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'facebook']) . '">Facebook</a></p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'twitter']) . '">Twitter</a></p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'linkedin']) . '">LinkedIn</a></p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'pinterest']) . '">Pinterest</a></p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'youtube']) . '">YouTube</a></p>' .
			'<p><a href="' . route('general::social.provider', ['provider' => 'instagram']) . '">Instagram</a></p>'
		);
	}

	/**
	 * Test for object_type attribute in json
	 *
	 * @param Request $request
	 */
	public function objectType(Request $request)
	{
		$obj = \App\Hub::get()->first();
		vd($obj->toArray());

		$obj = \App\User::get()->first();
		vd($obj->toArray());
	}

	/**
	 * Test for file system web path
	 *
	 * @param Request $request
	 */
	public function filesystemWebPath(Request $request)
	{
		$stored = FileStorage::where('status', '=', 'stored')->first();
		dd($stored->getWebPath());
	}

	/**
	 * Test for file system store
	 *
	 * @param Request $request
	 */
	public function filesystemStore(Request $request)
	{
		$object = PostAttachment::where('type', '=', 'image')->first();
		$stored = FileStorage::where('path', 'like', '%' . $object->resource)->first();
		$stored->object()->associate($object);
		$file = app(FileManager::class)->store($stored);
		dd($file);
	}

	/**
	 * Test for file system copy
	 *
	 * @param Request $request
	 */
	public function filesystemCopy(Request $request)
	{
		$url = 'http://rhfstone.staging.bodecontagion.com/wp-content/uploads/2017/10/products-header.jpg';
		$image = Image::make($url);
		$file = app(FileManager::class)->stage($image);
	}

	/**
	 * Test for fixLinks
	 *
	 * @param Request $request
	 */
	public function fixLinks(Request $request)
	{
		$text = 'This is a link <a href="https://google.com">some truncated links...</a> with <a href="somehashtaginks.com">#hashtags</a> and <a href="sometaglinks.com">@tags</a>';
		dd(fix_links($text, ['@', '#']));
	}

	/**
	 * Test for file system storage object
	 *
	 * @param Request $request
	 */
	public function filesystemStorage(Request $request)
	{
		$storage = Storage::disk('local.temp');
		dd($storage->getAdapter()->getPathPrefix());
	}

	/**
	 * Test for file system supported file types
	 *
	 * @param Request $request
	 */
	public function filesystemTypes(Request $request)
	{
		$fm = app(FileManager::class);
		dd($fm->getFileTypes(['video', 'data']));
	}

	/**
	 * Test for broadcast AccountConnected for user
	 *
	 * @param Request $request
	 */
	public function broadcastAccountConnected(Request $request)
	{
		$account = LinkedAccount::where('user_id', '=', $request->input('user_id'))
			->where('platform', '=', $request->input('platform'))
			->first();

		broadcast(new AccountConnected($account));
		vd($account);
	}

	/**
	 * Test for events
	 *
	 * @param Request $request
	 */
	public function events(Request $request)
	{
		$gig = new Gig();
		$hub = \App\Hub::find(1);
		$recipients = $hub->members->pluck('user');

		// fire event: event.gig.published
		event('event.gig.published', ['event' => 'event.gig.published', 'gig' => $gig, 'hub' => $hub, 'recipients' => $recipients]);
		die('notifications sent');
	}

	/**
	 * check user
	 *
	 * @param \Illimunate\Http\Request $request
	 */
	public function checkUser(Request $request)
	{
		$user = User::withTrashed()->where('email', '=', $request->input('email'))->first();
		dd($user);
	}

	/**
	 * restore soft deleted user
	 *
	 * @param \Illimunate\Http\Request $request
	 */
	public function restoreUser(Request $request)
	{
		$user = User::withTrashed()->where('email', '=', $request->input('email'))->first();
		$user->restore();
		dd($user);
	}

	/**
	 * send a test push notification to devices
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	public function sendPushNotification(Request $request)
	{
		$user = User::where('email', '=', $request->input('email'))->first();

		$pushBot = new PushBots;
		
		$appID = '5b35efde1db2dc0b147b691b';
		$appSecret = '265b4456f7eddf41f8675c519058d46f'; // 'd401f667825ba31a889f82040fb705fb'
		$pushBot->App($appID, $appSecret);

		// Notification Settings
		$pushBot->TokenOne('csYh_PAGoMQ:APA91bHCLOwrwBhE9SbT9ebKlkffLB2nS76Fi8k9ZjXh9Puy2yqWYGhMnAi2lmG9xwREuQ9dKIL-t0TedCqTK9QGObb5mhQkQx0RQ-2E6_Rmg4V4lWYXfgoYQs5e1KT_v3CdYpQesANsZMrRLhEHrq2jul-xizDwew'); 
		$pushBot->AlertOne("test message from laravel");
		$pushBot->PlatformOne("0");
		$pushBot->BadgeOne("+1");
		$pushBot->PayloadOne(array(
			'url' => url('/') . '/bodecontagion/gigs?ref=pb_pn'
		));
		$pushBot->PushOne();
	}

	/**
	 * send a test local notification to devices
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	public function sendPush(Request $request)
	{
		$user = User::where('email', '=', $request->input('email'))->first();

		$item = PushNotificationQueueItem::query()
			->where('user_id', '=', $user->id)
			->latest()
			->first();
	
		broadcast(new SendPush($item->load(['user', 'notification'])));
			
		vd($item);
	}

	/**
	 * send push notification using OneSignal
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	public function sendOneSignal(Request $request)
	{
		// get user
		$user = User::where('email', '=', $request->input('email'))->first();

		// get latest notification to send.
		$item = PushNotificationQueueItem::with(['user', 'notification'])
			->where('user_id', '=', $user->id)
			->latest()
			->first();

		$notification = $item->notification;

		$deviceToken = '6bd44696-60a1-40ce-9ec4-b07bbc306726';
		$url = 'influencerhub://bodecontagion/post/41'; // use influencerhub:// url scheme to launch the app.
		OneSignal::setParam('headings', [
				'en' => $notification->summary
			])
			->sendNotificationToUser($notification->message, $deviceToken, $url, $notification);
	}

	/**
	 * creates SHA-256 hash of a user's email address to verify the user's identity.
	 * This helps prevent users from impersonating one another by generating a user-specific token on your server, if you have one.
	 *
	 * @param \Illuminate\Http\Request $request
	 */
	public function oneSignalVerifyIdentity(Request $request)
	{
		// get user
		$user = User::where('email', '=', $request->input('email'))->first();

		if (is_null($user)) {
			die('No user with that email was found.');
		}
		$ONE_SIGNAL_REST_API_KEY = config('services.onesignal.rest_api_key');
		dd(hash_hmac('sha256', $user->email, $ONE_SIGNAL_REST_API_KEY));
	}

	/**
	 * force delete user
	 *
	 * @param \Illimunate\Http\Request $request
	 */
	public function forceDeleteUser(Request $request)
	{
		$user = User::withTrashed()->where('email', '=', $request->input('email'))->first();

		if (!is_null($user)) {
			$user->forceDelete();
		}
		dd($user);
	}

	/// Section: Utilities

	/**
	 * Test page to run a few commands as required
	 *
	 * @param Request $request
	 */
	public function commandRunner(Request $request)
	{
		// get commands
		$commands = [
			'migrate'                   => ['label' => 'migrate',                   'command' => 'migrate'],
			'migrate:reset'             => ['label' => 'migrate:reset',             'command' => 'migrate:reset'],
			'migrate:refresh'           => ['label' => 'migrate:refresh --seed',    'command' => 'migrate:refresh', 'args' => ['--seed' => true]],
			'migrate:rollback'          => ['label' => 'migrate:rollback',          'command' => 'migrate:rollback'],
			'posts:dispatch'            => ['label' => 'posts:dispatch',            'command' => 'posts:dispatch'],
			'gigfeeds:load'             => ['label' => 'gigfeeds:load',             'command' => 'gigfeeds:load'],
			'route:clear'               => ['label' => 'route:clear',               'command' => 'route:clear'],
			'route:cache'               => ['label' => 'route:cache',               'command' => 'route:cache'],
			'social:cache-pages'        => ['label' => 'social:cache-pages',        'command' => 'social:cache-pages'],
			'social:cache-connections'  => ['label' => 'social:cache-connections',  'command' => 'social:cache-connections'],
			'notification-types:create' => ['label' => 'notification-types:create', 'command' => 'notification-types:create']
		];

		// handle post back
		if($request->method() == 'POST') {

			// params
			$params = $request->all();

			// check password
			if(is_null(env('COMMAND_RUNNER_PASSWORD')) || $params['password'] != env('COMMAND_RUNNER_PASSWORD')) {
				$request->session()->flash('error', 'incorrect password');
				return redirect()->back();
			}

			// get command
			$command = $commands[$params['command']];
			$args = (isset($command['args']) && is_array($command['args'])) ? $command['args'] : [];

			// production check
			if(app()->environment() == 'production' && ((isset($command['production']) && $command['production'] !== true) || !isset($command['production']))) {
				$request->session()->flash('error', 'cannot run this command in production environment');
				return redirect()->back();
			}

			// run command
			$exitCode = Artisan::call($command['command'], $args);

			// response
			$request->session()->flash('success', 'command has run. exit code: ' . $exitCode);
			return redirect()->back();
		}

		// response
		$error   = $request->session()->pull('error');
		$success = $request->session()->pull('success');
		return view('utilities.command-runner', ['commands' => $commands, 'error' => $error, 'success' => $success]);
	}

	/// Section: Entry

	/**
	 * Entry into testing
	 *
	 * @param Request $request
	 * @param string  $method
	 * @return string
	 */
	public function index(Request $request, $method)
	{
		if(!is_null($method) && is_callable(array($this, $method), true, $callable_name)) {
			$response = $this->{$method}($request);
			if(!is_null($response)) {
				return $response;
			} else {
				echo '----------' . nl2br("\n");
				return $callable_name;
			}
		}
		return '';
	}

	// helpers

	/**
	 * build_data_files
	 *
	 * @param mixed $boundary
	 * @param mixed $files
	 * @param mixed $fields
	 * @return string
	 */
	private function build_data_files($boundary, $files, $fields = []){
		$data = '';
		$eol = "\r\n";

		$delimiter = '-------------' . $boundary;

		foreach ($fields as $name => $content) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $name . "\"".$eol.$eol
				. $content . $eol;
		}

		$data .= "--" . $delimiter . $eol
			. 'Content-Disposition: form-data; name="' . $files['name'] . '"; filename="' . $files['filename'] . '"' . $eol
			//. 'Content-Type: image/png'.$eol
			. 'Content-Transfer-Encoding: binary'.$eol
		;

		$data .= $eol;
		$data .= $files['content'] . $eol;

		$data .= "--" . $delimiter . "--".$eol;


		return $data;
	}

}
