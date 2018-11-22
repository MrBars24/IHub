<?php

namespace App\Http\Controllers\General;

// App
use App\Entity;
use App\Http\Controllers\Controller;
use App\Hub;
use App\User;
use App\Membership;
use App\LoginHistory;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\CheckSlugRequest;

// Laravel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

// 3rd party
use JavaScript;

class AuthController extends Controller
{
	/**
	 * POST /login/store
	 * ROUTE general::login.store [api.php]
	 *
	 * Store login information for better reporting
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function storeLogin(Request $request)
	{
		// get auth user
		$auth_user = auth()->user();

		// params
		$params = $request->only('oauth_token', 'device_token', 'device_os');

		// check point: must specify oauth_token
		if(!isset($params['oauth_token'])) {
			abort(500, 'Must specify oauth_token');
		}

		// create login history item or do nothing if one already exists
		// @todo: can also store ip_address, user_agent
		LoginHistory::firstOrCreate([
			'oauth_token' => $params['oauth_token']
		], $params + [
			'user_id' => $auth_user->id,
			'user_agent' => $request->header('User-Agent'),
			'ip_address' => $request->ip()
		]);

		// store oauth_token in session
		//session(['oauth_token33' => $params['oauth_token']]);
	
		$expires_at = carbon()->addDays(config('auth.expire.access_token'));
		$expiration = carbon()->diffInMinutes($expires_at); // we'll get the minutes
		// make httponly = false so we can access it via javascript
		$cookie = cookie('oauth_token', $params['oauth_token'], $expiration, null, null, false, false);

		// response
		$data = [
			'data' => [],
			'route' => 'general::login.store',
			'success' => true
		];
		return response()->json($data)->cookie($cookie);
	}

	/**
	 * POST /api/login/mobile
	 * ROUTE general::login.mobile [api.php]
	 *
	 * Store login information for better reporting
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function mobileLogin(Request $request)
	{
		// get auth user
		$auth_user = auth()->user();

		// params
		$params = $request->only('oauth_token', 'device_token', 'device_os');

		// check point: must specify oauth_token
		if(!isset($params['oauth_token'])) {
			abort(500, 'Must specify oauth_token');
		}

		// get the latest login information
		// i think we should also compare the user agent here..
		$loginHistory = LoginHistory::where('oauth_token', '=', $params['oauth_token'])
			->where('user_id', $auth_user->id)
			->latest()
			->first();
		
		// loginHistory must not be null.
		if (!is_null($loginHistory)) {
			$loginHistory->device_token = $params['device_token'];
			$loginHistory->device_os = $params['device_os'];
			$loginHistory->save();
		}

		// response
		$data = [
			'data' => [
				'login_history' => $loginHistory
			],
			'route' => 'general::login.mobile',
			'success' => true
		];
		return response()->json($data);
	}


	/**
	 * GET /api/onesignal/verify
	 * ROUTE general::onesignal.verify [api.php]
	 *
	 * creates SHA-256 hash of a user's email address to verify the user's identity.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function verifiyIdentity(Request $request)
	{
		// get auth user
		$auth_user = auth()->user();

		// get ONE_SIGNAL_API_KEY
		$ONE_SIGNAL_REST_API_KEY = config('services.onesignal.rest_api_key');

		// create hash
		$hash = hash_hmac('sha256', $auth_user->email, $ONE_SIGNAL_REST_API_KEY);

		// response
		$data = [
			'data' => [
				'hash' => $hash
			],
			'route' => 'general::onesignal.verify',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/entity
	 * ROUTE general::entity [api.php]
	 *
	 * The entity info end point
	 * Expected query string parameters:
	 * - hub (slug, optional)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function getEntity(Request $request)
	{
		// get auth user
		$auth_user = auth()->user();

		// get optional hub parameter
		$hub = Hub::where('slug', '=', $request->input('hub'))->first();

		// @leo: for viewing the hub as master that acts like a hubmanager?..
		if($auth_user->is_master && !is_null($hub)) {
			$auth_user = User::find($hub->manager->user_id);
			$auth_user->makeVisible('from_master');
			$auth_user->from_master = true;
		}

		// checkpoint: user authenticated
		if(is_null($auth_user)) {
			$data = [
				'data' => [
					'hub' => $hub->toArray(),
					'referrer' => $request->url()
				],
				'route' => 'general::entity',
				'success' => false,
				'error_reason' => 'Unauthenticated'
			];
			return response()->json($data);
		}

		// get entity
		$auth_user->makeVisible('is_master');
		$entity = $auth_user;

		if(!is_null($hub) && $auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$entity = $hub;
		}

		// load user info if hub is specified
		if(!is_null($hub)) {
			$auth_user->load([
				'membership' => function($query) use ($hub) {
						$query->select([
							'user_id', 'points', 'role', 'accepted_conditions'
						])
							->where('hub_id', '=', $hub->id);
					}
			]);
		}

		// assign original as auth user
		$entity->setRelation('original', $auth_user);

		// response
		$data = [
			'data' => [
				'entity' => $entity->toArray(),
				'hub' => !is_null($hub) ? $hub->toArray() : null
			],
			'route' => 'general::entity',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /password-reset
	 * ROUTE general::password-reset [web.php]
	 * 
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function sendPasswordLink(ForgotPasswordRequest $request)
	{
		// validate then send password reset email
		$passwordResponse = Password::sendResetLink($request->only('email'), function($message) {
			$message
				->from('noreply@influencerhub.com')
				->subject('Influencer HUB: Your Password Reset Link');
		});

		// generate the message
		$message = 'Please check your email to reset your password.';
		if(!($passwordResponse == Password::RESET_LINK_SENT)) {
			$message = 'The user could not be found.';
		}

		// response
		$data = [
			'data' => [
				'message' => $message
			],
			'route' => 'general::password-reset',
			'success' => ($passwordResponse == Password::RESET_LINK_SENT)
		];
		return response()->json($data);
	}

	/**
	 * GET /reset-password
	 * ROUTE general::reset-password [web.php]
	 * 
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response 
	 */
	public function passwordReset(ResetPasswordRequest $request)
	{
		// validate then reset the password
		$resetResponse = Password::reset($request->all(), function($user, $password) {
			// set the password
			$user->password = $password;
			$user->save();
		});

		// generate the message
		$message = 'Password has been changed, you can now login using your new password.';
		if(!($resetResponse === Password::PASSWORD_RESET)) {
			$message = 'Failed to change your password.';
		}

		// response
		$data = [
			'data' => [
				'message' => $message
			],
			'route' => 'general::password-reset',
			'success' => $resetResponse === Password::PASSWORD_RESET
		];
		return response()->json($data); 

	}

	/**
	 * GET /social/loggedin
	 * ROUTE general::social.loggedin
	 */
	public function loggedin(Request $request)
	{
		$access_token = $request->session()->get('access_token');
		$refresh_token = $request->session()->get('refresh_token');
		$expires_in = $request->session()->get('expires_in');
		$token_type = $request->session()->get('token_type');
		$flashedData = [];

		// check if the session has access_token in it
		if (!is_null($access_token)) {
			$loginData = [
				'access_token' => $access_token,
				'refresh_token' => $refresh_token,
				'expires_in' => $expires_in,
				'token_type' => $token_type
			];
			
			$flashedData = [
				'oauth_tokens' => $loginData
			];
		}

		JavaScript::put($flashedData);
		return view('account-loggedin2', $flashedData);
	}

	/**
	 * POST /check-slug
	 * ROUTE general::check-slug [api.php]
	 * check if slug was already taken
	 * slug validation is done automatically via request
	 * @param  CheckSlugRequest $request [description]
	 * @return [type]                    [description]
	 */
	public function checkSlug(CheckSlugRequest $request)
	{
		$data = [
			'route' => 'hub::check-slug', 
			'success' => true
		];
		return response()->json($data);
	}
}
