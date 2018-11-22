<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

// 3rd party
use Jenssegers\Agent\Agent;

class HandleLink
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);
		
		// redirect to app custom URL scheme if the user is on mobile device.
		if ($this->canRedirect($request, $response)) {

			$uri = 'influencerhub://app-redirect?url=/' . $request->path();

			return redirect(route('general::handle-link', [
				'uri' => $uri,
				'fallback' => $request->url(),
				'redirected' => true
			]));
		}
		
		return $response;
	}

	private function canRedirect($request, $response)
	{		
		$agent = new Agent();
		
		// get content types
		$type = $response->headers->get('Content-Type');
		// does content type contains html
		$isHTML = Str::contains($type, ['/html']);

		$wasRedirected = !$request->input('redirected');

		return $agent->isMobile() && $isHTML && $request->route()->getName() != 'general::handle-link' && $wasRedirected;
	}
}
