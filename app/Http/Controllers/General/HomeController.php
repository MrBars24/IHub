<?php

namespace App\Http\Controllers\General;

// App
use App\Alert;
use App\Http\Controllers\Controller;

// Laravel
use Illuminate\Http\Request;

// 3rd party
use JavaScript;

class HomeController extends Controller
{
	/**
	 * GET /
	 * ROUTE general::entry [web.php]
	 *
	 * Show the application
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function entry(Request $request)
	{
		$flashedData = [];
		$access_token = $request->session()->get('access_token');
		$refresh_token = $request->session()->get('refresh_token');
		$expires_in = $request->session()->get('expires_in');
		$token_type = $request->session()->get('token_type');
		$flash_message = $request->session()->get('flash_message');
		$display_name = $request->session()->get('display_name');
		$profile_picture = $request->session()->get('profile_picture');
		$redirect_url = $request->session()->get('redirect_url');

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

		if (!is_null($redirect_url)) {
			$flashedData = [
				'redirect_url' => $redirect_url,
				'display_name' => $display_name,
				'profile_picture' => $profile_picture,
			];
		}


		// check if the sessions has error message that needs to be rendered in vue
		if (!is_null($flash_message)) {
			$flashedData = [
				'flash_message' => $flash_message['messages']
			];
		}

		// response
		$data = [
			'endpoint' => $request->url()
		];
		
		// add login data, flashed errors, flashed sessions
		$data += $flashedData;
		// append all flashed data into javascript code.
		JavaScript::put($flashedData);
		return view('entry', $data);
	}

	public function handleLink(Request $request)
	{
		$uri = $request->all();
		
		// response
		return view('link')->with($uri);
	}
}
