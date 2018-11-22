<?php

namespace App\Http\Middleware;

// Laravel
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

// App
use App\Hub;
use App\User;

class HubMember
{
	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// checkpoint: valid user
		if($this->auth->guest()) {// this should not be null if 'auth' middleware was used prior
			return $this->unauthorised($request);
		}

		// Get route model bound hub object
		$hub = $request->route('hub'); // it returns hub_slug instead of hub object
		if (!is_object($hub)) {
			$hub = Hub::where('slug', $hub)->first();
		}

		// checkpoint: valid hub
		if(is_null($hub)) {
			return $this->unauthorised($request);
		}

		// get auth user membership
		$user = $this->auth->user();
		$user->load([
			'membership' => function($query) use ($hub) {
				$query->where('hub_id', '=', $hub->id)
					->where('is_active', '=', true);
			}
		]);

		// checkpoint: valid membership
		if(!$user->is_master && is_null($user->membership)) {
			return $this->unauthorised($request);
		}

		if($user->is_master) {
			$request->auth_user = User::find($hub->manager->user_id);
			$request->auth_user->from_master = true;
		} else {
			$request->auth_user = $user;
		}

		return $next($request);
	}

	/**
	 * Return invalid response.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return mixed
	 */
	private function unauthorised(Request $request)
	{
		if($request->ajax()) {
			return response('Unauthorized.', 401);
		} else {
			return redirect()->guest(route('auth::login', array('r' => $request->url())));
		}
	}
}