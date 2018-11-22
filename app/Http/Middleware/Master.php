<?php

namespace App\Http\Middleware;

// Laravel
use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;

class Master
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
	public function handle($request, Closure $next)
	{
		// connect auth user with provider user
		$auth_user = session_user();

		// check user auth if empty
		if(is_null($auth_user)){
			return redirect('/');
		}else if(!$auth_user->is_master){
			return redirect('/');
		}
		
		return $next($request);
	}
}
