<?php

namespace App\Http\Controllers\Master;

// Laravel
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
	/**
	* GET /master
	* ROUTE master::hub.master [web.php]
	*
	* The dashboard page
	*
	* @param  \Illuminate\Http\Request  $request
	* @return \Illuminate\Http\Response
	*/
	public function index(Request $request)
	{
		// response
		$data = [
			'active_sidebar' => '', // no active sidebar is selected
			'auth_user' => session_user() // user auth data
		];
		return view('master.dashboard', $data);
	}
}
