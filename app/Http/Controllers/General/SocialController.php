<?php

namespace App\Http\Controllers\General;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Membership;
use App\Session;
use App\User;
use App\Events\Social\AccountConnected;

// Laravel
use Illuminate\Http\Request;
use Socialize;
use Illuminate\Support\Facades\URL;

// 3rd Party
use JavaScript;

class SocialController extends Controller
{
	/**
	 * GET /social/{provider}
	 * ROUTE general::social.provider [web.php]
	 *
	 * Connect using laravel socialite
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string                    $provider
	 * @return \Illuminate\Http\Response
	 */
	public function provider(Request $request, $provider)
	{
		// old id, used for replacing an account
		session(['oid' => $request->input('oid')]);

		// flag socialite action
		$action = $request->input('socialite-action', 'link-account');
		session(['socialite-action' => $action]);

		// set hub session
		$hub = $request->input('hub', null);
		session(['hub' => $hub]);

		// session_user. passing only in Mobile build. 
		// @todo: try to make this more secure by hashing the id on mobile client. 
		// or maybe pass a token instead of user_id.
		$session_user = $request->input('session_user', null);
		session(['session_user' => $session_user]);

		// is the request came from mobile?
		$device = $request->input('device', null);
		session(['device' => $device]);

		if($this->isSupported($provider)) {

			// store referrer
			$hub_slug = $request->input('hub'); 
			// im not sure but laravel url/routes don't seem to work with hash mode 
			// should we really point them to /api/{hub}/settings/account ?..
			// $referrer = $hub_slug ? route('hub::settings', ['hub' => $hub_slug, 'tab' => 'account']) : URL::previous();
			// $referrer = $hub_slug ? url('/') . "/$hub_slug/settings/account" : URL::previous();

			// NOTE: i think we should just use URL::previous()..
			$referrer = URL::previous();
			session(['referrer' => $referrer]);

			switch($provider) {
				case 'facebook':
					return $this->provideFacebook($provider);
					break;
				case 'twitter':
					return $this->provideDefault($provider);
					break;
				case 'linkedin':
					return $this->provideLinkedIn($provider);
					break;
				case 'pinterest':
					return $this->providePinterest($provider);
					break;
				case 'youtube':
					return $this->provideYouTube($provider);
					break;
				case 'instagram':
					return $this->provideInstagram($provider);
					break;
			}
		}
		abort(404);
	}

	/**
	 * GET /social/{provider}/callback
	 * ROUTE general::social.callback [web.php]
	 *
	 * Callback url to be called from external site
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  string                    $provider
	 * @return \Illuminate\Http\Response
	 */
	public function callback(Request $request, $provider)
	{
		// get socialite action
		$action = session('socialite-action', 'link-account'); // possible values: link-account, login, signup

		switch($action) {
			case 'link-account':
				return $this->callbackLinkAccount($provider);
				break;
			case 'login':
				return $this->callbackLogin($provider);
				break;
			case 'signup':
				return $this->callbackSignup($provider);
				break;
		}
		abort(404);
	}

	/// Section: Providers

	protected function provideFacebook($provider)
	{
		// $scopes = ['user_friends', 'publish_actions', 'manage_pages', 'publish_pages'];
		$scopes = ['user_friends', 'manage_pages', 'publish_pages']; // 'publish_actions permission has been removed by Facebook.
		return Socialize::with($provider)->scopes($scopes)->stateless()->redirect();
	}

	protected function provideLinkedIn($provider)
	{
		$scopes = ['r_basicprofile', 'r_emailaddress', 'w_share', 'rw_company_admin'];
		return Socialize::with($provider)->scopes($scopes)->stateless()->redirect();
	}

	protected function providePinterest($provider)
	{
		$scopes = ['read_public', 'write_public', 'read_relationships'];
		return Socialize::with($provider)->scopes($scopes)->stateless()->redirect();
	}

	protected function provideYouTube($provider)
	{
		$scopes = [
			'readonly' => 'https://www.googleapis.com/auth/youtube.readonly',
			'account'  => 'https://www.googleapis.com/auth/youtube',
			'write'    => 'https://www.googleapis.com/auth/youtube.upload',
		];
		return Socialize::with($provider)->scopes($scopes)->with(['approval_prompt' => 'force', 'access_type' => 'offline'])->stateless()->redirect();
	}

	protected function provideInstagram($provider)
	{
		return Socialize::with($provider)->scopes(['follower_list'])->stateless()->redirect();
	}

	protected function provideDefault($provider)
	{
		return Socialize::with($provider)->redirect();
	}

	/// Section: Callbacks

	protected function callbackLinkAccount($provider)
	{
		// get provider user from socialite

		$hub = session('hub');
		$referrer = is_null(session('referrer')) ? url('/') : session('referrer');

		try {

			// i will try to do a stateless social authorization here because we have a hybrid app that uses this endpoint.
			// https://isaacearl.com/blog/stateless-socialite
			// NOTE: only twitter have no stateless authorization.
			if ($provider == 'twitter') {
				$user = Socialize::with($provider)->user();
			}
			else {
				$user = Socialize::with($provider)->stateless()->user();
			}

		} catch(\Exception $e) {
			
			$messages = ['Error connecting account.'];
			// response
			$data = [
				'flash_message' => [
					'section' => 'link-account',
					'type' => 'error',
					'messages' => $messages,
				],
			];
			// need to do this for other failed response.
			if ($this->isConnectedViaMobile()) {
				// attach data to javascript `window` variable
				JavaScript::put($data);
				// return view('account-linked', $accountData);
				return view('account-linked2', $data);
			}
			return redirect($referrer)->with($data);
		}

		// connect auth user with provider user
		$auth_user = session_user();

		// fallback to user by sessionId. ----- where did you define sessionId ?
		// if(is_null($auth_user) && !is_null(session('sessionId'))) {
		// 	$sessionId = session('sessionId');

		// 	// get session
		// 	$findSession = Session::find($sessionId);
		// 	if(!$findSession->user_id){
		// 		return 'User not found';
		// 	}

		// 	// get user
		// 	$auth_user = User::find($findSession->user_id);
		// }

		// fallback to session_user (user_id)
		if (is_null($auth_user) && !is_null(session('session_user'))) {
			$session_user = session('session_user');

			// get user
			$auth_user = User::find($session_user);
		}

		// connect account
		$account = null;
		if (!is_null($auth_user)) {
			$account = $auth_user->connectAccount($provider, $user);
		}
		else {
			$messages = 'Error occurred while connecting account, please try again.';
			// error response
			$data = [
				// 'result' => (object) [
				'flash_message' => [
					'section' => 'link-account',
					'type' => 'error',
					'messages' => $messages,
				],
			];
			return redirect($referrer)->with($data);
		}

		// redirect back
		if ($this->isConnectedViaMobile()) {
			$accountData = [
				'account' => [
					'data' => $account,
					'provider' => $provider
				]
			];
			// attach data to javascript `window` variable
			JavaScript::put($accountData);
			// return view('account-linked', $accountData);
			return view('account-linked2', $accountData);
		}
		return redirect($referrer); // pass account in here?
	}

	protected function callbackLogin($provider)
	{

		// NOTE: temporary fix 
		// @todo: below is a workaround a laravel redirect bug where url('/') resolves to localhost?? to fix later
		$is_mobile = session('device', null) == 'mobile';
		$redirector = $is_mobile ? env('APP_URL') . route('general::account.loggedin', [], false) : url('/');
		
		// get provider user from socialite
		try {
			$user = Socialize::with($provider)->stateless()->user();
		} catch(\Exception $e) {
			$messages = ['Error occurred while logging in, please try again.'];

			// response
			$data = [
				// 'result' => (object) [
				'flash_message' => [
					'section' => 'login-via-socialite',
					'type' => 'error',
					'messages' => $messages,
				],
			];

			// return redirect()->route('general::login')->with($data);
			return redirect($redirector)->with($data); // must be hardcoded
		}

		// get user from database
		$authUser = User::query()
			->where('email', '=', $user->email)
			->where('is_active', '=', true)
			->first();

		// check if user was found
		if(is_null($authUser)) { // redirect to account-setup instead?
			$messages = ['Could not find user. Please make sure your social account email address matches your Influencer HUB account email address'];
			// response
			$data = [
				'flash_message' => [
					'section' => 'login-via-socialite',
					'type' => 'error',
					'messages' => $messages,
				],
			];
			return redirect($redirector)->with($data);
		}

		$tokens = $this->getAccessToken($user);

		// redirect back to entry file to pass the tokens into client side.
		return redirect($redirector)->with($tokens);
	}

	private function callbackSignup($provider)
	{
		// @note: laravel redirect sometimes causing a bug that it's not redirecting you to the proper url.
		// get previous referrer
		$ref = session('referrer');
		$hub = session('hub');
		$redirector = url('/');

		// get provider user from socialite
		try {
			$user = Socialize::with($provider)->stateless()->user();
		} catch(\Exception $e) {
			$messages = ['Error occurred while signing up, please try again.'];

			// response
			$data = [
				'flash_message' => [
					'section' => 'signup-via-socialite',
					'type' => 'error',
					'messages' => $messages,
				],
				'redirect_url' => url()->previous(),
			];
			return redirect($redirector)->with($data);
		}

		// get user from database
		$authUser = User::query()
			->where('email', '=', $user->email)
			->where('is_active', '=', false)
			->first();

		// check if user was found
		if(is_null($authUser)) {
			$messages = ['Could not find user. Please make sure your social account email address matches your Influencer HUB account email address'];

			// response
			$data = [
				'flash_message' => [
					'section' => 'signup-via-socialite',
					'type' => 'error',
					'messages' => $messages,
				],
				'redirect_url' => url()->previous(),
			];

			return redirect($redirector)->with($data);
		}
		else {
			// delete the token to log the user out.
			$tokens = \DB::table('oauth_access_tokens')
		  	->where('user_id', $authUser->id)->delete();
		}

		// get hub
		$hub = Hub::query()
			->where('slug', '=', $hub)
			->where('is_active', '=', true)
			->first();

		// get linked membership
		$membership = Membership::query()
			->where('user_id', '=', $authUser->id)
			->where('hub_id', '=', $hub->id)
			->where('status', '=', 'pending')
			->where('is_active', '=', false)
			->first();

		// check if membership was found
		if(is_null($membership)) {
			$messages = ['Could not find associated user membership. Please get in touch with the hub manager that you were invited to'];

			// response
			$data = [
				'flash_message' => [
					'section' => 'signup-via-socialite',
					'type' => 'error',
					'messages' => $messages,
				],
				'redirect_url' => url()->previous(),
			];

			return redirect($redirector)->with($data);
		}

		// prepare account
		$authUser->name = $user->name;
		$authUser->save();

		// redirect back
		// session([
		// 	'display_name' => $user->name,
		// 	'profile_picture' => $user->avatar_original,
		// ]);

		$data = [
			'display_name' => $user->name,
			'profile_picture' => $user->avatar_original,
			'redirect_url' => URL::previous()
		];

		return redirect($redirector)->with($data);
	}

	/// Section: Helpers

	/**
	 * send a request to oauth endpoints to get access_tokens
	 * @param  \App\User $user     
	 * @param  string $provider provider name @default facebook
	 * @return array
	 */
	protected function getAccessToken($user, $provider = 'facebook')
	{
		// send a request to /oauth/token to get access_token
		$proxy = Request::create(
			'/oauth/token',
			'POST',
			[
				'grant_type' => 'social',
				'client_id' => '3',
				'client_secret' => env('CLIENT_SECRET', 'berg709KSLpaqgXS6yQJyBqVeoqz4rBWlNrYzlXc'),
				'network' => $provider,
				'access_token' => $user->token, // facebook token
			]
		);

		$response = app()->handle($proxy);
		$tokens = json_decode((string) $response->getContent(), true);

		return $tokens;
	}

	protected function isSupported($provider)
	{
		return in_array($provider, [
			'facebook',
			'twitter',
			'linkedin',
			'pinterest',
			'youtube',
			'instagram',
		]);
	}

	/**
	 * check if the request came from mobile app.
	 * 
	 * @return bool
	 */
	private function isConnectedViaMobile()
	{
		return !is_null(session('session_user'));
	}
}
