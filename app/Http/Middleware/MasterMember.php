<?php

namespace App\Http\Middleware;

// Laravel
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

class MasterMember
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

		// get auth user membership
		$user = $this->auth->user();

		// checkpoint: valid membership
		if(!$user->is_master) {
			return $this->unauthorised($request);
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