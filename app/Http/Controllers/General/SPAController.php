<?php

namespace App\Http\Controllers\General;

// App
use App\Post;
use App\Hub;

// Laravel
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SPAController extends Controller
{
	public function postView(Request $request, Hub $hub, Post $post)
	{
		return $this->getRoute($request);
	}

	private function getRoute(Request $request)
	{
		return redirect($request->fullUrl());
	}
}
