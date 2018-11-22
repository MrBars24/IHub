<?php

namespace App\Http\Controllers\Master;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Membership;
use App\User;

// Laravel
use Illuminate\Http\Request;
use Carbon\Carbon;

// Traits
use App\Traits\ListAction;

class HubController extends Controller
{
	use ListAction;

	/// Section: Properties

	protected $valid_actions = [
		'deactivate', 'activate', 'delete', 'hub_remove', 'reset_points', 'reset_all', 'user_promote'
	];

	/// Section: Controller Actions

	/**
	 * GET /master/hubs/
	 * ROUTE master::hubs.index [web.php]
	 *
	 * The hubs index page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		// count join queries
		$count_influencer = \DB::table('membership')
			->selectRaw('hub_id, count(*) as member_count')
			->where('role', '=', 'influencer')
			->groupBy('hub_id');

		$count_post = \DB::table('post')
			->selectRaw('hub_id, count(*) as post_count')
			->where('is_published', '=', true)
			->groupBy('hub_id');

		// get hubs
		// active hubs
		$active_hubs = Hub::with([
			'manager' => function($query) {
				$query->where('role', '=', 'hubmanager');
			},
			'manager.user'
		])
			->leftJoin(\DB::raw(' ( ' . $count_influencer->toSql() . ' ) AS cnt_member '), 'cnt_member.hub_id', '=', 'hub.id')
			->mergeBindings($count_influencer)
			->leftJoin(\DB::raw(' ( ' . $count_post->toSql() . ' ) AS cnt_post '), 'cnt_post.hub_id', '=', 'hub.id')
			->mergeBindings($count_post)
			->where('hub.is_active', '=', true)
			->paginate(10);
		$active_hubs->each(function($item) {
			// activated_at
			$item->activated_at_formatted = $item->activated_at ? $item->activated_at->format('dS M Y') : null;
		});

		// inactive hubs
		$inactive_hubs = Hub::with([
			'manager' => function($query) {
				$query->where('role', '=', 'hubmanager');
			},
			'manager.user'
		])
			->leftJoin(\DB::raw(' ( ' . $count_influencer->toSql() . ' ) AS cnt_member '), 'cnt_member.hub_id', '=', 'hub.id')
			->mergeBindings($count_influencer)
			->leftJoin(\DB::raw(' ( ' . $count_post->toSql() . ' ) AS cnt_post '), 'cnt_post.hub_id', '=', 'hub.id')
			->mergeBindings($count_post)
			->where('hub.is_active', '=', false)
			->paginate(10);
		$inactive_hubs->each(function($item) {
			// deactivated_at
			$item->deactivated_at_formatted = $item->deactivated_at ? $item->deactivated_at->format('dS M Y') : null;
		});

		// response
		$data = [
			'active_hubs' => $active_hubs,
			'inactive_hubs' => $inactive_hubs
		];
		if(!is_ajax()) {
			$data += [
				'active_hubs_table' => with(new Hub)->newCollection(),
				'inactive_hubs_table' => with(new Hub)->newCollection(),
			];
			return view('master.hub.index', $data);
		}
		return response()->json($data);
	}

	/**
	 * GET /master/{hub}/action
	 * ROUTE master::hubs.memberaction [web.php]
	 *
	 * @todo: please add a description here for this controller action
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  Hub $hub
	 * @return \Illuminate\Http\Response
	 */
	public function memberAction(Request $request, Hub $hub)
	{
		// pass to list action method
		$request->session()->flash('activeTab', 'memberTab');
		return $this->action($request, $hub);
	}

	/**
	 * GET /master/hubs/create
	 * ROUTE master::hubs.create [web.php]
	 *
	 * @param  Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		// response
		$data = [
			'hub' => new Hub,
			'form_action' => 'Master\HubController@store'
		];
		return view('master.hub.create', $data);
	}

	/**
	 * POST /master/hubs/create
	 * ROUTE master::hubs.store [web.php]
	 *
	 * @param Request $request
	 * @return void
	 */
	public function store(Request $request)
	{
		// validation
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email'
		]);

		// saving hub
		$hub = new Hub;
		$hub->name = $request->input('name');
		$hub->email = $request->input('email');
		$hub->save();

		// response
		return redirect()->route('master::hub.index');
	}

	/**
	 * GET /master/hubs/edit/{hub}
	 * ROUTE master::hubs.edit [web.php]
	 *
	 * The edit hub page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, Hub $hub)
	{
		//access hub members
		$members = $hub->members()
			->where("role", "=", "influencer")
			->with(['user'])
			->paginate(20);

		if($members->count() > 0) {
			$members->each(function($item) {
				// joined_at
				$item->joined_at_formatted = $item->joined_at ? $item->joined_at->format('dS M Y') : null;
			});
		}

		$detailActive = ($request->session()->get('activeTab', 'null') == 'null') ? true : false;
		
		// response
		$data = [
			'hub' => $hub,
			'members' => $members,
			'hub_members_table' => with(new Hub)->newCollection(),
			'tabs' => [
				'id' => 'hub-details',
				'items' => [
					'details' => ['label' => 'Details', 'active' => $detailActive],
					'members' => ['label' => 'Members', 'active' => !$detailActive]
				],
			],
			'form_action' => 'Master\HubController@update'
		];

		return view('master.hub.item', $data);
	}

	/**
	 * PUT /master/hubs/edit/{hub}
	 * ROUTE master::hub.update [web.php]
	 *
	 * @param Request $request
	 * @param Hub $hub
	 * @return void
	 */
	public function update(Request $request, Hub $hub)
	{
		// validation
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email'
		]);

		// update hub
		$hub->name = $request->input('name');
		$hub->email = $request->input('email');
		$hub->is_active = ($request->input('is_active') ? true : false);
		$hub->save();

		// response
		return redirect()->back();
	}

	/// Section: Helpers

	/**
	 * Action: deactivate
	 * Deactivate selected hubs
	 *
	 * @param  Request  $request
	 * @param  array    $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionDeactivate(Request $request, $ids = [])
	{
		// get count
		$count = Hub::whereIn('id', $ids)
			->where('is_active', '=', true) // check if hub is actually active
			->count(); // get count of users

		$response = redirect()->back();

		// check if count matches
		if($count !== count($ids)) {
			$response->with('error', 'Hub deactivation failed!');
		} else {
			// perform action
			Hub::whereIn('id', $ids)
			->update([
				'is_active' => false
			]);

			$response->with('success', 'Hub deactivated!');
		}

		// response
		return $response;
	}

	/**
	 * Action: activate
	 * Activate selected hubs
	 *
	 * @param  Request  $request
	 * @param  array    $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionActivate(Request $request, $ids = [])
	{
		// get count
		$count = Hub::whereIn('id', $ids)
			->where('is_active', '=', false) // check if hub is actually inactive
			->count(); // get count of users
		
		$response = redirect()->back();

		// check if count matches
		if($count !== count($ids)) {
			$response->with('error', 'Hub activation failed!');
		} else {
			// perform action
			Hub::whereIn('id', $ids)
				->update([
					'is_active' => true
				]);

			$response->with('success', 'Hub Activated!');
		}

		// response
		return $response;
	}

	/**
	 * Action: delete
	 * Delete selected hubs
	 *
	 * @param  Request  $request
	 * @param  array    $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionDelete(Request $request, $ids = [])
	{
		// get count
		$count = Hub::whereIn('id', $ids)
			->where('is_active', '=', false) // check if hub is actually inactive
			->count(); // get count of users
		
		$response = redirect()->back();

		// check if count matches
		if($count !== count($ids)) {
			$response->with('error', 'Hub failed to be deleted!');
		} else {
			// perform action
			Hub::whereIn('id', $ids)
				->delete();
				
			$response->with('success', 'Hub successfully deleted!');
		}

		// response
		return $response;
	}

	/**
	 * Action : Hub Remove
	 * Remove memberber to a hub
	 *
	 * @param  Request $request
	 * @param  Hub $hub
	 * @param  array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionHubRemove(Request $request, Hub $hub, $ids = [])
	{
		// get count
		$count = Membership::whereIn('id', $ids)
			->where('hub_id', '=', $hub->id)
			->count(); // get count of members

		$response = redirect()->back();

		// check if count matches
		if($count !== count($ids)) {
			$response->with('error', 'Member failed to be remove on hub!');
		} else {
			// perform action
			Membership::whereIn('id', $ids)
				->where('hub_id', '=', $hub->id)
				->delete();

			$response->with('success', 'Member removed on hub!');
		}

		// response
		return $response;
	}

	/**
	 * Action : Reset Point
	 * Reset Points of a specific member
	 *
	 * @param  Request $request
	 * @param  Hub $hub
	 * @param  array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionResetPoints(Request $request, Hub $hub, $ids = [])
	{
		// get count
		$count = Membership::whereIn('id', $ids)
			->where('hub_id', '=', $hub->id)
			->count(); // get count of users

		$response = redirect()->back();

		// check if count matches
		if($count !== count($ids)) {
			$response->with('error', 'Member failed to reset points!');
		} else {
			// perform action
			Membership::whereIn('id', $ids)
				->where('hub_id', '=', $hub->id)
				->update([
					'points' => 0
				]);
			
			$response->with('success', 'Member points reset success!');
		}

		// response
		return $response;
	}

	/**
	 * Action : Reset All Points
	 * Reset All Points of member on a specific hub
	 *
	 * @param  Request $request
	 * @param  Hub $hub
	 * @param  array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionResetAll(Request $request, Hub $hub, $ids)
	{
		// perform action
		Membership::where('hub_id', '=', $hub->id)
			->update([
				'points' => 0
			]);

		// response
		return redirect()->back()->with('success', 'Members points reset success!');
	}

	/**
	 * Action : User Promote
	 * Promote User to Hub Manager
	 *
	 * @param  Request $request
	 * @param  Hub $hub
	 * @param  array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionUserPromote(Request $request, Hub $hub, $ids)
	{
		// add validation here
		$count = Membership::whereIn('id', $ids)
			->where('hub_id', '=', $hub->id)
			->count(); // get count of users
		$response = redirect()->back();

		// check if count match with the request and database row
		if($count !== count($ids)) {
			$response->with('error', 'Member promotion failed!');
		} else {

			if(count($ids) > 1) {
				$response->with('error', 'Only one member can be promted!');
			} else {
				// perform action

				// Demotes the current hubmanager 
				$hub->manager->role = 'influencer';
				$hub->manager->save();

				// Promotes the selected member to hubmanager
				Membership::whereIn('id', $ids)
				->where('hub_id', '=', $hub->id)
				->update([
					'role' => 'hubmanager'
					]);
					
					
				$user = User::find($ids[0]);
				$user->hubmanager_at = Carbon::now()->toDateTimeString();
				$user->save();
					
				// reflect new user email to hub email
				$hub->email = $hub->manager->user->email;
				$hub->save();

				$response->with('success', 'Member promoted as Hub Manager!');
			}

		}

		// response
		return $response;
	}
}