<?php

namespace App\Http\Controllers\General;

// App
use App\Http\Controllers\Controller;
use App\Hub;

// Laravel
use Illuminate\Http\Request;

class HubController extends Controller
{
	/**
	 * GET /api/hub/list
	 * ROUTE general::hub.list [api.php]
	 *
	 * The hub list endpoint
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function getList(Request $request)
	{
		// auth user
		$auth_user = auth()->user();

		// get hubs
		$hubs = Hub::query()
			->select([
				'hub.id', 
				'hub.name', 
				'hub.slug', 
				'hub.profile_picture',
				'hub.profile_picture_cropping', 
				'hub.filesystem',
				'hub.branding_header_colour',
				'hub.branding_header_colour_gradient',
				'hub.branding_header_logo',
				'hub.branding_primary_button',
				'hub.branding_primary_button_text'
			])
			->join('membership', 'hub.id', '=', 'membership.hub_id')
			->where('user_id', '=', $auth_user->id)
			->where('membership.is_active', '=', true)
			->where('hub.is_active', '=', true)
			->get();

		// response
		$data = [
			'data' => [
				'hubs' => $hubs->toArray()
			],
			'route' => 'general::hub.list',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/hub/select/{hub}
	 * ROUTE general::hub.select [api.php]
	 *
	 * The select hub action
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function selectHub(Request $request, Hub $hub)
	{
		// NOTE: api dev, i'm not sure if i coded this right. if no, please change. thanks
		// By @Eric
		// $user = $request->user();
		$data = [
			'data' => [
				'hub' => $hub
			],
			'route' => 'general::hub.select', 
			'success' => true
		];
		return response()->json($data);
	}
}
