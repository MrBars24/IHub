<?php

namespace App;

// App
use App\Components\CommonTrait;
use App\Components\EntityInterface;
use App\Components\FileContextInterface;
use App\Components\FileContextTrait;
use App\Components\SluggableInterface;
use App\Components\SluggableTrait;
use App\Components\PassportToken;
use App\Components\PictureTrait;
use App\Modules\Files\FileManager;
use App\Notifications\ResetPasswordNotification;

// Laravel
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cookie;

// 3rd Party
use Stevebauman\EloquentTable\TableTrait;

class User extends Authenticatable implements FileContextInterface, SluggableInterface, EntityInterface
{
	// App
	use CommonTrait, FileContextTrait, SluggableTrait, PassportToken, PictureTrait;

	// Laravel
	use Notifiable, HasApiTokens, SoftDeletes;

	// 3rd Party
	use TableTrait;

	/// Section: Schema

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'user';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 
		'email', 
		'password', 
		'summary',
		'profile_picture',
		'cover_picture',
		'profile_picture_cropping',
		'cover_picture_cropping',
		'profile_picture_display',
		'is_master'
	];

	/**
	 * The attributes that should be visible in arrays.
	 *
	 * @var array
	 */
	protected $visible = [
		'id',
		'name',
		'slug',
		'profile_picture',
		'membership',
		'accounts',
		'email',
		'profile_picture_tiny',
		'profile_picture_medium',
		'cover_picture_web_path',
		'created_at',
		'created_at_formatted',
		'hubs',
		'user_edit',
		'is_active'
	];

	/**
	 * The accessors to append to the model's array form.
	 */
	protected $appends = [
		'cover_picture_web_path',
		'original_profile_picture_web_path',
		'original_cover_picture_web_path',
		'profile_picture_tiny',
		'profile_picture_medium',
		'hubs',
		'user_edit'
	];

	/**
	 * The attributes that should be automatically casted
	 */
	protected $casts = [
		'profile_picture_cropping' => 'array',
		'cover_picture_cropping' => 'array'
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'password_raw', 'remember_token', 'is_master'];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'last_login_at',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	/**
	 * Get the route key for the model.
	 *
	 * @return string
	 */
	public function getRouteKeyName()
	{
		return 'slug';
	}

	/// Section: Relations

	public function membership()
	{
		return $this->hasOne(Membership::class);
	}

	public function posts()
	{
		return $this->morphMany(Post::class, 'author');
	}

	public function oauth_tokens()
	{
		return $this->hasMany(OauthAccessToken::class);
	}

	public function accounts()
	{
		return $this->hasMany(LinkedAccount::class);
	}

	public function login_histories()
	{
		return $this->hasMany(LoginHistory::class);
	}

	/// Section: Mutators

	public function setPasswordAttribute($value)
	{
		// ensure password value exists
		if(strlen($value) > 0) {
			$this->attributes['password'] = bcrypt($value);
			if(app()->environment() == 'local') {
				$this->password_raw = $value;
			}
		}
	}

	/**
	 * ATTRIBUTE hubs
	 * @return string
	 */
	public function getHubsAttribute()
	{
		$user_hub = [];
		$hubs = $this->membership()->with(['hub'])->get();

		if(count($hubs) > 0) {
			foreach($hubs as $hub){
				if(isset($hub->hub->name)) {
					array_push($user_hub, $hub->hub->name);
				}
			}
		}

		return implode(", ", $user_hub);

	}

	/**
	 * ATTRIBUTE master_edit
	 * @return string
	 */
	public function getUserEditAttribute()
	{
		// check if master
		if($this->is_master){
			return route('master::staff.edit', [$this]); // if master use staff edit route
		}else{
			return route('master::user.edit', [$this]); // if not master use user edit route
		}
	}

	/// Section: Methods

	/**
	 * invite by Email
	 * - create the user
	 * - send email to that email address
	 * @param  string  $email   [description]
	 * @param  \App\Hub $hub    [description]
	 * @param  boolean $viaBulkImport [description]
	 * @return [type]           [description]
	 */
	public static function inviteByEmail($email, $hub, $viaBulkImport = false)
	{
		// user
		// check if the email is already existing.

		$user = User::with(['membership' => function($query) use ($hub) {
			$query->where('hub_id', $hub->id);
		}])
			->where('email', $email)
			->first();

		// no user at all.
		// - create user
		// - create membership
		if (is_null($user)) {
			$user = new User;
			$user->email = $email; 
			$user->is_taggable = false;
			$user->save();
			$user->setRelation('membership', null); // placeholder
		}

		// user found. so
		// - create just the membership
		if (is_null($user->membership)) {
			$membership = Membership::invitedByEmail($user, $hub);
			$user->setRelation('membership', $membership);

			// separte the email sending if it's a bulk import.
			// -- TODO: try to do this using call_user_func. it will be a lot cleaner.
			
			$data = [
				'membership' => $user->membership,
				'hub' => $hub,
			];

			// get hubmanager
			$hubManagers = User::query()
				->leftJoin('membership', 'user.id', '=', 'membership.user_id')
				->where('membership.hub_id', '=', $hub->id)
				->where('membership.role', '=', 'hubmanager')
				->where('membership.is_active', '=', true)
				->where('user.is_active', '=', true)
				->first();
	
			// construct email 
			$hub_email = !is_null($hubManagers) ? $hubManagers->email : 'noreply@influencerhub.com';
			$user_email = $user->email;
			$hub_name = $hub->name;
			// send email
			Mail::send('email.invite', $data, function($message) use ($user_email, $hub_name, $hub_email) {
			// send platform information
				$fromName = $hub_name . ' via Influencer HUB';

			// compile message
				$message
					->from($hub_email, $fromName) // ->from('noreply@influencerhub.com')
					->to($user_email)
					->subject("You have been invited to join the $hub_name Influencer Hub.");
			});
			return $user;
		}
		// already a member
		else {
			return false;
		}
	}

	/**
	 * invite imported email list
	 * @param  [type] $emails [description]
	 * @param  [type] $hub    [description]
	 * @return [type]         [description]
	 */
	public static function inviteListByEmail($emails, $hub)
	{
		$users = User::with(array(
			'membership' => function($query) use ($hub) {
				$query->where('hub_id', '=', $hub->id);
			}
		))
		    ->whereIn('email', $emails)
			->get()
			->keyBy('email');
		$invitedUsers = [];


		// get hubmanager
		$hubManagers = User::query()
			->leftJoin('membership', 'user.id', '=', 'membership.user_id')
			->where('membership.hub_id', '=', $hub->id)
			->where('membership.role', '=', 'hubmanager')
			->where('membership.is_active', '=', true)
			->where('user.is_active', '=', true)
			->first();

		$hub_email = !is_null($hubManagers) ? $hubManagers->email : 'noreply@influencerhub.com';

		foreach($emails as $email) {
			$user = isset($users[$email]) ? $users[$email] : null;
			if (is_null($user)) {
				$user = self::inviteByEmail($email, $hub, true);
				// construct email 
				$data = [
					'membership' => $user->membership,
					'hub' => $hub,
				];
				$user_email = $user->email;
				$hub_name = $hub->name;
				// send email
				Mail::queue('email.invite', $data, function($message) 
					use ($user_email, $hub_name, $hub_email) {
					// send platform information
					$fromName = $hub_name . ' via Influencer HUB';

					// compile message
					$message
						->from($hub_email, $fromName) // ->from('noreply@influencerhub.com')
						->to($user_email)
						->subject("You have been invited to join the $hub_name Influencer Hub.");
				});
				array_push($invitedUsers, $user);
			}
		}
		return $invitedUsers;
	}

	/**
	 * Query for session user using session oauth_token
	 *
	 * @param $query
	 * @return \App\User
	 */
	public function scopeGetSessionUser($query)
	{
		//$token = session('oauth_token');
		$token = isset($_COOKIE['oauth_token']) ? $_COOKIE['oauth_token'] : null;
		return $query
			->select('user.*')
			->join('login_history', 'login_history.user_id', '=', 'user.id')
			->where('oauth_token', '=', $token);
	}

	/**
	 * Get the user's membership to a specific hub
	 *
	 * @param \App\Hub $hub
	 * @return mixed
	 */
	public function getMembershipTo(Hub $hub)
	{
		if(!$this->relationLoaded('membership')) {
			$this->load([
				'membership' => function($query) use ($hub) {
					$query->where('hub_id', '=', $hub->id);
				}
			]);
		}
		return $this->membership;
	}

	/**
	 * Send the password reset notification.
	 *
	 * @param  string  $token
	 * @return void
	 */
	public function sendPasswordResetNotification($token)
	{
		// create default hub for email layout
		$hub = new Hub;
		$hub->email_header_colour = '#20272d';
		$hub->email_footer_colour = '#20272d';
		$hub->email_footer_text_1 = '#ffffff';
		$hub->email_footer_text_2 = '#5e5e5e';
		$hub->branding_primary_button = '#f5a194';
		$hub->branding_primary_button_text = '#ffffff';

		$user_email = $this->email;
		$data = [
			//'hub' => Hub::first(), // this is only temporary since we don't have a template for general email
			'hub' => $hub, // just use a new hub instance here
			'token' => $token
		];
		Mail::send('email.password', $data, function($message) use ($user_email) { // @todo: queueing emails doesn't work for some reason?
			// compile message
			$message
				->from('noreply@influencerhub.com')
				->to($user_email)
				->subject("Influencer HUB: Your Password Reset Link");
		});
	}

	/// Section: Passport

	public function findForPassport($identifier) {
		return $this->orWhere('email', $identifier)
			->orWhere('slug', $identifier)->first();
	}

	/// Section: Socialite

	public function connectAccount($platform, $providerUser)
	{
		// create new account
		$account = null;
		if($platform == 'facebook') {
			$account = $this->connectFacebook($providerUser);
		} elseif($platform == 'twitter') {
			$account = $this->connectTwitter($providerUser);
		} elseif($platform == 'linkedin') {
			$account = $this->connectLinkedIn($providerUser);
		} elseif($platform == 'pinterest') {
			$account = $this->connectPinterest($providerUser);
		} elseif($platform == 'youtube') {
			$account = $this->connectYouTube($providerUser);
		} elseif($platform == 'instagram') {
			$account = $this->connectInstagram($providerUser);
		}
		return $account;
	}

	protected function getAccount($platform)
	{
		$account = LinkedAccount::query()
			->withTrashed() // support the soft deleted account to renew it
			->where('user_id', '=', $this->id)
			->where('platform', '=', $platform)
			->first();
		if(is_null($account)) {
			$account = new LinkedAccount;
			$account->platform = $platform;
		} else {
			// reset values
			$account->deleted_at = null;
			$account->expired_at = null;
			$account->expired_reason = null;
		}
		return $account;
	}

	protected function connectFacebook($providerUser)
	{
		$account = $this->getAccount('facebook');
		$account->is_enabled = true;
		$account->name = $providerUser->name;
		$account->native_id = $providerUser->id;
		$account->token = $providerUser->token;
		$this->getFacebookFollowers($account);
		$account->followers_label = 'friends';
		$this->accounts()->save($account);
		return $account;
	}

	protected function getFacebookFollowers($account)
	{
		//https://developers.facebook.com/docs/facebook-login/permissions/v2.2#reference-publish_actions
		$native_id    = $account->native_id;
		$access_token = $account->token;
		$graph_url = 'https://graph.facebook.com/' . urlencode($native_id) . '/friends' . '?access_token=' . urlencode($access_token);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $graph_url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		// result
		$friends = curl_exec($ch);
		$friends = json_decode($friends);

		if(isset($friends->summary)) {
			$account->followers = $friends->summary->total_count;
		}

		curl_close($ch);
	}

	protected function connectTwitter($providerUser)
	{
		$account = $this->getAccount('twitter');
		$account->is_enabled = true;
		$account->name = '@' . $providerUser->nickname;
		$account->native_id = $providerUser->user['id_str'];
		$account->token = $providerUser->token;
		$account->secret = $providerUser->tokenSecret;
		$account->followers = $providerUser->user['followers_count'];
		$account->followers_label = 'followers';
		$this->accounts()->save($account);
		return $account;
	}

	protected function connectLinkedIn($providerUser)
	{
		$account = $this->getAccount('linkedin');
		$account->is_enabled = true;
		$account->name = $providerUser->name;
		$account->native_id = $providerUser->user['id'];
		$account->token = $providerUser->token;
		$account->followers_label = 'connections';
		$this->accounts()->save($account);
		return $account;
	}

	protected function connectPinterest($providerUser)
	{
		$account = $this->getAccount('pinterest');
		$account->is_enabled = true;
		$account->name = $providerUser->name;
		$account->native_id = $providerUser->id;
		$account->token = $providerUser->token;
		$account->followers_label = 'followers';
		$this->accounts()->save($account);
		return $account;
	}

	protected function connectYouTube($providerUser)
	{
		$tokenArray = $providerUser->accessTokenResponseBody;

		// ensure created is set
		if(!isset($tokenArray['created'])) {
			$tokenArray['created'] = time();
		}

		// @note: etag info here: http://stackoverflow.com/questions/21752421/youtube-api-v3-and-etag
		$account = $this->getAccount('youtube');
		$account->is_enabled = true;
		$account->name = $providerUser->nickname;
		$account->native_id = $providerUser->id;
		$account->token = json_encode($tokenArray);
		$account->followers_label = 'subscribers';
		$this->accounts()->save($account);
		return $account;
	}

	protected function connectInstagram($providerUser)
	{
		$account = $this->getAccount('instagram');
		$account->is_enabled = true;
		$account->name = $providerUser->nickname;
		$account->native_id = $providerUser->id;
		$account->token = $providerUser->token;
		$account->followers = $providerUser->user['counts']['followed_by'];
		$account->followers_label = 'followers';
		$this->accounts()->save($account);
		return $account;
	}

	/// Section: FileContextTrait

	/**
	 * The prefix to add to the file context value
	 *
	 * @return string
	 */
	public function getFileContextPrefix()
	{
		return 'u';
	}
	
	/// Section: EntityInterface

	/**
	 * Retrieve the owners of this entity
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getOwners()
	{
		// check cache
		if(!is_null($this->owners)) {
			return $this->owners;
		}

		// get filtered members
		$filtered = collect([$this->id => $this]);

		// cache
		$this->owners = $filtered;

		return $filtered;
	}

	protected $owners = null;
}
