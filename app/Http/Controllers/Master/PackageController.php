<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PackageController extends Controller
{
	/**
	* GET /master/packages
	* ROUTE master::package.index [web.php]
	*
	* The package page
	*
	* @param  \Illuminate\Http\Request $request
	* @return \Illuminate\Http\Response
	*/
	public function index(Request $request)
	{
		// response
		$data = [
			'active_sidebar' => '', // no active sidebar is selected
			'auth_user' => session_user() // user auth data
		];

		return view('master.package.index', $data);
	}
}
