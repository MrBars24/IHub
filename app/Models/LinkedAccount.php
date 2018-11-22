<?php

namespace App;

// App
use App\Components\CommonTrait;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// 3rd Parry
use Codebird\Codebird;
use LinkedIn\LinkedIn as LinkedInApiExchange;

class LinkedAccount extends Model// implements PlatformInterface
{
	// App
	use CommonTrait;

	// Laravel
	use SoftDeletes;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'linked_account';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['platform'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'linked_at',
		'expired_at',
		'deleted_at'
	];

	/// Section: Relations

	public function token_info() // @todo: rename to tokenInfo for consistency
	{
		return $this->belongsTo(LinkedAccount::class, 'id', 'id');
	}

	public function api_paging() // @todo: rename to apiPaging for consistency
	{
		return $this->hasMany(ApiPaging::class, 'token', 'token');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	/// Section: Query Scopes

	/**
	 * Scope for getting API info for link account native id
	 *
	 * @param $query
	 * @param string $platform
	 * @return mixed
	 */
	public function scopeApiInfo($query, $platform)
	{
		// get linked accounts
		// - self referencing (after aggregate query)
		// - active accounts
		// - for active users
		// - group by native_id
		return $query->with([
			'token_info' => function($query) {
					$query->select('id', 'native_id', 'token', 'secret', 'platform');
				},
		   'token_info.api_paging' => function($query) {
				   $query->select('id', 'token', 'platform', 'next_page', 'end_point_type');
			   },
		])
			->leftJoin('user', 'linked_account.user_id', '=', 'user.id')
			->whereNull('linked_account.expired_at')
			->where('linked_account.is_enabled', '=', true)
			->where('user.is_active', '=', true)
			->where('linked_account.platform', '=', $platform)
			->select([
				'linked_account.name',
				'linked_account.platform',
				'linked_account.native_id',
				\DB::raw('MIN(linked_account.id) AS id'),
			])
			->groupBy('native_id');
	}

	/// Section: Mutators

	// @todo: check if this method and other methods are used
	public function scopeTaggable($query, $native_id)
	{
		$builder = $query->where('native_id', $native_id);
		$platform = $account->first()->platform;


		return $builder->where('native_id', '=', $native_id)
			->where(function($query) {

			});
	}

	public function getOtherClassesAttribute()
	{
		switch($this->platform) {
			case 'facebook':
				$classes = 'open-facebook-messagebox load-facebook-select-pages';
				break;
			case 'twitter':
				$classes = 'validate-twitter-post';
				break;
			case 'pinterest':
				$classes = 'load-pinterest-select-boards';
				break;
			case 'youtube':
				$classes = 'load-youtube-options';
				break;
			case 'instagram':
				//$classes = 'visible-pg-instagram-installed after-instagram-publish';
				$classes = '';
				break;
			default: // other
				$classes = '';
				break;
		}
		return $classes;
	}

	public function getOtherAttributesAttribute()
	{
		$hub = app()->make(Hub::class);
		switch($this->platform) {
			case 'facebook':
				$attributes = ' data-dialog-content="facebook-select-pages" data-get-facebook-pages="' . route('hub::facebook.pages', array('hub' => $hub->slug)) . '"';
				break;
			case 'pinterest':
				$attributes = ' data-dialog-content="pinterest-select-boards" data-get-pinterest-boards="' . route('hub::pinterest.boards', array('hub' => $hub->slug)) . '"';
				break;
			case 'youtube':
				$attributes = ' data-dialog-content="youtube-options" data-get-youtube-categories="' . route('hub::youtube.categories', array('hub' => $hub->slug)) . '"';
				break;
			default: // other
				$attributes = '';
				break;
		}
		return $attributes;
	}

	/// Section: Social Access

	/**
	 * Retrieve facebook page data for the linked account from the following end points:
	 * - user/accounts
	 *
	 * @return array
	 */
	public function queryFacebookPages()
	{
		// api components
		$token     = $this->token;
		$native_id = $this->native_id;

		// api params: get facebook pages
		$endpoint = 'https://graph.facebook.com/' . urlencode($native_id) . '/accounts';
		$data = [
			'access_token' => $token,
		];

		// send request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . http_build_query(array_filter($data, 'strlen')));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$output = curl_exec($ch);
		curl_close($ch);

		// get response
		$reply = json_decode($output);

		// get pages that have the permission 'CREATE_CONTENT'
		$pages = collect($reply->data)->filter(function($item) {
			$perms = $item->perms;
			$item->type = 'page';
			return in_array('CREATE_CONTENT', $perms);
		});

		// build data array
		$data = [];
		foreach($pages as $page) {
			$data[] = (object) [
				'profile_id'     => $page->id,
				'access_token'   => $page->access_token,
				'name'           => $page->name,
				'type'           => $page->type,
				'avatar'         => null,
				'follower_count' => null,
			];
		}
		return $data;
	}

	/**
	 * Retrieve pinterest board data for the linked account from the following end points:
	 * - user/boards
	 *
	 * @return array
	 */
	public function queryPinterestBoards()
	{
		// api components
		$token = $this->token;

		// api params: get pinterest boards
		$endpoint = 'https://api.pinterest.com/v1/me/boards/'; // @note: NEED to have the ending slash in this end point, otherwise it processes as a redirect
		$data = [
			'access_token' => $token,
		];

		// send request
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint . '?' . http_build_query(array_filter($data, 'strlen')));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($ch);
		curl_close($ch);

		// get response
		$reply = json_decode($output);

		// get boards
		$boards = collect($reply->data);

		// build data array
		$data = [];
		foreach($boards as $board) {
			$data[] = (object) [
				'profile_id'     => $board->id,
				'access_token'   => $token, // we'll use the account access token here
				'name'           => $board->name,
				'type'           => 'board',
				'avatar'         => null,
				'follower_count' => null,
			];
		}
		return $data;
	}

	/**
	 * Retrieve facebook page data for the linked account from the following end points:
	 * - user/accounts
	 *
	 * @return array
	 */
	public function queryLinkedinCompanies()
	{
		// api components
		$token     = $this->token;
		$native_id = $this->native_id;

		// api params: get linkedin companies
		$url = '/companies:(id,name,description,square-logo-url)';
		$postData = [
			'is-company-admin' => 'true'
		];
		$method = LinkedInApiExchange::HTTP_METHOD_GET;
		$headers = [];

		// linkedin api
		$settings = [
			'api_key' => config('services.linkedin.client_id'),
			'api_secret' => config('services.linkedin.client_secret'),
			'callback_url' => config('services.linkedin.redirect'),
		];
		$linkedin = new LinkedInApiExchange($settings);
		$linkedin
			->setAccessToken($token)
			->setState($native_id);
		$reply = $linkedin->fetch($url, $postData, $method, $headers);

		// convert to object
		$reply = json_decode(json_encode($reply), false);

		// go through each company and set some values
		$companies = collect($reply->values)->each(function($item) {
			$item->type = 'company';
		});

		// build data array
		$data = [];
		foreach($companies as $company) {
			$data[] = (object) [
				'profile_id'     => $company->id,
				'access_token'   => $token, // for now, we'll use the access token that was responsible for this request
				'name'           => $company->name,
				'type'           => $company->type,
				'avatar'         => $company->squareLogoUrl,
				'follower_count' => null,
			];
		}
		return $data;
	}

	/**
	 * Retrieve youtube category data for the linked account from the following end points:
	 * - list video categories
	 *
	 * @return array
	 */
	public static function queryYouTubeCategories()
	{
		// get any available youtube linked account
		$account = self::query()
			->where('platform', '=', 'youtube')
			->where('is_enabled', '=', true)
			->whereNull('expired_at')
			->orderBy('linked_at', 'DESC')
			->first();

		if(is_null($account)) {
			return [];
		}

		$token = $account->token;

		// the scopes required for the video post
		$scopes = [
			'https://www.googleapis.com/auth/youtube',
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
		$client->setAccessToken($token);

		// refresh
		$token = $account->handleYoutubeAccessToken($client);
		if($token === false) {
			return [];
		}

		// youtube
		$youtube = new \Google_Service_YouTube($client);
		$categories = $youtube->videoCategories->listVideoCategories('snippet', ['regionCode' => 'AU']);
		if(!is_null($categories)) {
			$categories = $categories->getItems();
		}

		// build data array
		// need to filter by 'snippet.assignable', otherwise the post dispatch process will throw an invalid category error
		$categories = collect($categories)->where('snippet.assignable', true)->pluck('snippet.title', 'id');

		$data = [];
		foreach($categories as $key => $text) {
			$data[] = (object) [
				'native_id' => $key,
				'title'     => $text,
			];
		}
		return $data;
	}

	/**
	 * Refresh the YouTube access token if required.
	 *
	 * @param $client
	 * @return bool
	 */
	public function handleYoutubeAccessToken($client)
	{
		$accessToken = $client->getAccessToken();

		// no access token?
		if(is_null($accessToken)) {
			return false;
		}

		// detect expired token
		if($client->isAccessTokenExpired()) {
			$accessToken = json_decode($accessToken);

			if(!isset($accessToken->refresh_token)) {
				return false;
			}

			$refreshToken = $accessToken->refresh_token;
			$client->refreshToken($refreshToken);
			$newAccessToken = $client->getAccessToken();

			// save to database
			$this->token = $newAccessToken;
			$this->save();

			return $newAccessToken;
		}
		return true;
	}

	/**
	 * Retrieve facebook connection data for the linked account from the following end points:
	 * - user/likes
	 * - user/accounts
	 *
	 * @return object
	 */
	public function queryFacebookConnections()
	{
		// api components
		$token     = $this->token;
		$native_id = $this->native_id;
		$platform  = $this->platform;
		$next_page = $this->api_paging->filter(function($item) use ($platform) {
			return $platform == $item->platform;
		})->keyBy('end_point_type')->toArray();

		// build data array
		$result = ['data' => [], 'next_page' => []];

		// end_point_type: user/likes
		$end_point_type = 'user/likes';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default url, or stored "next_page" if exists
			$endpoint = 'https://graph.facebook.com/' . urlencode($native_id) . '/likes';
			$data = [
				'access_token' => $token,
				'fields' => 'id,name,username,picture'
			];
			$endpoint = $endpoint . '?' . http_build_query(array_filter($data, 'strlen'));

			// get next page
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$endpoint = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = '';
			}

			// send request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			// get response
			$reply = json_decode($output);
			$likes = collect($reply->data);

			// build data array
			$next = (isset($reply->paging) && isset($reply->paging->next) && strlen($reply->paging->next) > 0) ? $reply->paging->next : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($likes as $like) {
				$result['data'][] = (object) [
					'profile_id'     => $like->id,
					'access_token'   => '',
					'display_name'   => $like->name,
					'screen_name'    => isset($like->username) ? $like->username : '',
					'type'           => 'page',
					'avatar'         => $like->picture->data->url,
					//'follower_count' => null,
					'end_point_type' => $end_point_type
				];
			}
		}

		// end_point_type: user/accounts
		$end_point_type = 'user/accounts';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default url, or stored "next_page" if exists
			$endpoint = 'https://graph.facebook.com/' . urlencode($native_id) . '/accounts';
			$data = [
				'access_token' => $token,
				'fields' => 'id,name,username,picture'
			];
			$endpoint = $endpoint . '?' . http_build_query(array_filter($data, 'strlen'));

			// get next page
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$endpoint = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = '';
			}

			// send request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			// get response
			$reply = json_decode($output);
			$accounts = collect($reply->data);

			// build data array
			$next = (isset($reply->paging) && isset($reply->paging->next) && strlen($reply->paging->next) > 0) ? $reply->paging->next : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($accounts as $account) {
				$result['data'][] = (object) [
					'profile_id'     => $account->id,
					'access_token'   => '',
					'display_name'   => $account->name,
					'screen_name'    => isset($account->username) ? $account->username : '',
					'type'           => 'page',
					'avatar'         => $account->picture->data->url,
					//'follower_count' => null,
					'end_point_type' => $end_point_type
				];
			}
		}

		// convert to object and return
		return (object) $result;
	}

	/**
	 * Retrieve twitter connection data for the linked account from the following end points:
	 * - followers/list
	 * - friends/list
	 *
	 * @return object
	 */
	public function queryTwitterConnections()
	{
		// api components
		$platform  = $this->platform;
		$next_page = $this->api_paging->filter(function($item) use ($platform) {
			return $platform == $item->platform;
		})->keyBy('end_point_type')->toArray();

		// build twitter api client
		Codebird::setConsumerKey(config('services.twitter.client_id'), config('services.twitter.client_secret'));
		$cb = Codebird::getInstance();
		$cb->setToken($this->token, $this->secret);

		// build data array
		$result = ['data' => [], 'next_page' => []];

		// end_point_type: followers/list
		$end_point_type = 'followers/list';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default cursor, or stored cursor if exists
			$cursor = '-1';
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$cursor = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = $cursor;
			}

			// send request and get response
			$reply = $cb->followers_list([
				'user_id' => $this->native_id,
				'stringify_ids' => 'true',
				'cursor' => $cursor,
			]);

			// break if error encountered
			if(isset($reply->errors)) {
				break;
			}

			// build data array
			$next = (isset($reply->next_cursor) && strlen($reply->next_cursor) > 0) ? $reply->next_cursor : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($reply->users as $user) {
				$result['data'][] = (object) [
					'profile_id'     => $user->id_str,
					'access_token'   => '',
					'display_name'   => $user->name,
					'screen_name'    => $user->screen_name,
					'type'           => 'profile',
					'avatar'         => $user->profile_image_url,
					'follower_count' => $user->followers_count,
					'end_point_type' => $end_point_type
				];
			}
		}

		// end_point_type: friends/list
		$end_point_type = 'friends/list';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default cursor, or stored cursor if exists
			$cursor = '-1';
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$cursor = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = $cursor;
			}

			// send request and get response
			$reply = $cb->friends_list([
				'user_id' => $this->native_id,
				'stringify_ids' => 'true',
				'cursor' => $cursor,
			]);

			// break if error encountered
			if(isset($reply->errors)) {
				break;
			}

			// build data array
			$next = (isset($reply->next_cursor) && strlen($reply->next_cursor) > 0) ? $reply->next_cursor : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($reply->users as $user) {
				$result['data'][] = (object) [
					'profile_id'     => $user->id_str,
					'access_token'   => '',
					'display_name'   => $user->name,
					'screen_name'    => $user->screen_name,
					'type'           => 'profile',
					'avatar'         => $user->profile_image_url,
					'follower_count' => $user->followers_count,
					'end_point_type' => $end_point_type
				];
			}
		}

		// convert to object and return
		return (object) $result;
	}

	/**
	 * Retrieve twitter connection data for the linked account from the following end points:
	 * - users/self/followed-by
	 * - users/self/follows
	 *
	 * @return object
	 */
	public function queryInstagramConnections()
	{
		// api components
		$token     = $this->token;
		$native_id = $this->native_id;
		$platform  = $this->platform;
		$next_page = $this->api_paging->filter(function($item) use ($platform) {
			return $platform == $item->platform;
		})->keyBy('end_point_type')->toArray();

		// build data array
		$result = ['data' => [], 'next_page' => []];

		// end_point_type: users/self/followed-by
		$end_point_type = 'users/self/followed-by';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default url, or stored "next_url" if exists
			$endpoint = 'https://api.instagram.com/v1/users/self/followed-by';
			$data = [
				'scope' => 'follower_list',
				'access_token' => $token,
			];
			$endpoint = $endpoint . '?' . http_build_query(array_filter($data, 'strlen'));

			// get next page
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$endpoint = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = '';
			}

			// send request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			// get response
			$reply = json_decode($output);
			$users = collect($reply->data);

			// build data array
			$next = (isset($reply->next_url) && strlen($reply->next_url) > 0) ? $reply->next_url : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($users as $user) {
				$result['data'][] = (object) [
					'profile_id'     => $user->id,
					'access_token'   => '',
					'display_name'   => $user->full_name,
					'screen_name'    => isset($user->username) ? $user->username : '',
					'type'           => 'profile',
					'avatar'         => $user->profile_picture,
					//'follower_count' => null,
					'end_point_type' => $end_point_type
				];
			}
		}

		// end_point_type: users/self/follows
		$end_point_type = 'users/self/follows';
		$count = 5;
		for($i = 0; $i < $count; $i++) {

			// load from default url, or stored "next_url" if exists
			$endpoint = 'https://api.instagram.com/v1/users/self/follows';
			$data = [
				'access_token' => $token,
			];
			$endpoint = $endpoint . '?' . http_build_query(array_filter($data, 'strlen'));

			// get next page
			if(isset($next_page[$end_point_type]) && strlen($next_page[$end_point_type]['next_page']) > 0) {
				$endpoint = $next_page[$end_point_type]['next_page'];
			} else {
				$next_page[$end_point_type]['next_page'] = '';
			}

			// send request
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$output = curl_exec($ch);
			curl_close($ch);

			// get response
			$reply = json_decode($output);
			$users = collect($reply->data);

			// build data array
			$next = (isset($reply->next_url) && strlen($reply->next_url) > 0) ? $reply->next_url : '';
			$result['next_page'][$end_point_type] = $next_page[$end_point_type]['next_page'] = $next;
			foreach($users as $user) {
				$result['data'][] = (object) [
					'profile_id'     => $user->id,
					'access_token'   => '',
					'display_name'   => $user->full_name,
					'screen_name'    => isset($user->username) ? $user->username : '',
					'type'           => 'profile',
					'avatar'         => $user->profile_picture,
					//'follower_count' => null,
					'end_point_type' => $end_point_type
				];
			}
		}

		// convert to object and return
		return (object) $result;
	}

	/**
	 * Attempt to expire the account if it fits certain message criteria
	 *
	 * @param $message
	 */
	public function expire($message)
	{
		if($message instanceof \Exception) {
			$message = $message->getMessage();
		}
		if(
			// condition 1
			(strpos(strtolower($message), 'access') !== false || strpos(strtolower($message), 'request') !== false || strpos(strtolower($message), 'expired token') !== false) &&
			// condition 2
			strpos(strtolower($message), 'token') !== false &&
			// condition 3
			(strpos(strtolower($message), 'expired') !== false || strpos(strtolower($message), 'invalid') !== false || strpos(strtolower($message), 'unable to verify') !== false || strpos(strtolower($message), 'error validating') !== false || strpos(strtolower($message), 'revoked by the user') !== false)
			// and not expired already
			&& is_null($this->expired_at)
		) {
			$this->expired_at = $this->freshTimestamp();
			$this->expired_reason = $message;
			$this->save();

			// send notification

			// get first hub
			/*
			$account->load([
				'user.memberships' => function($query) {
					$query->where('is_active', '=', true);
				},
				'user.memberships.hub'
			]);

			// send notification
			// unless we can't identify the user, membership or the hub
			if(!is_null($account->user) && !is_null($account->user->memberships) && $account->user->memberships->count() > 0) {
				$hub = $account->user->memberships->first()->hub;
				NotificationType::createNotificationByType($hub, 'account.expired', $account->user, $account->user, array('platform' => $account->platform, 'user' => $account->user));
			}
			*/
		}
	}

	/// Section: Events

	/**
	 * The "booting" method of the model.
	 *
	 * @return void
	 */
	protected static function boot()
	{
		parent::boot();

		// Model event: LinkedAccount->creating
		self::creating(function($obj) {
			$obj->linked_at = $obj->freshTimestamp();
		});
	}
}
