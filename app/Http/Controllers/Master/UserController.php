<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// Models
use App\User;

// Traits
use App\Traits\ListAction;

class UserController extends Controller
{
	use ListAction;

	/// Section: Properties

	protected $valid_actions = [
		'deactivate', 'activate', 'delete', 'hubs_remove'
	];

	/**
	 * GET /master/users/
	 * ROUTE master::user.index [web.php]
	 *
	 * The master user index page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		// get active users
		$active_user = User::where('is_master', '=', false)
			->where('is_active', '=', true)
			->paginate(50);

		// get inactive users
		$inactive_user = User::where('is_master', '=', false)
			->where('is_active', '=', false)
			->paginate(50);

		// add formatted date for active user
		$active_user->each(function($item) {
			// creted_at
			$item->created_at_formatted = $item->created_at ? $item->created_at->format('dS M Y') : null;
		});

		// add formatted date for inactive user
		$inactive_user->each(function($item) {
			// created_at
			$item->created_at_formatted = $item->created_at ? $item->created_at->format('dS M Y') : null;
		});

		// response
		$data = [
			'active_users' => $active_user,
			'inactive_users' => $inactive_user,
			'users_table' => with(new User)->newCollection()
		];

		return view('master.user.index', $data);
	}

	/**
	 * GET /master/users/create
	 * ROUTE master::user.create [web.php]
	 * 
	 * The master user create page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		// response
		$data = [
			'user' => new User,
			'form_action' => 'Master\UserController@store'
		];

		return view('master.user.create', $data);
	}

	/**
	 * POST /master/users/create
	 * ROUTE master::user.store [web.php]
	 *
	 * The master user create action
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		// validation
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email|unique:user,email',
			'password' => 'required|min:8',
			'confirm_password' => 'required|same:password'
		]);

		// create user
		User::create($request->all());

		// response
		return redirect()->back()->with('success', 'User successfully been created.');
	}

	/**
	 * GET /master/users/edit/{user}
	 * ROUTE master::user.edit [web.php]
	 *
	 * The master user edit page
	 * 
	 * @param Request $request
	 * @param User $user
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, User $user)
	{
		// get member current hub
		$hubs = $user->membership()
			->where('status', '=', 'member')
			->where('is_active', '=', true)
			->with(['hub'])
			->get()
			->map(function($u){
				return $u->hub()
					->with(['manager', 'manager.user'])
					->first();
			});

		// get member pending hub
		$pending_hubs = $user->membership()
			->where('status', '=', 'pending')
			->where('is_active', '=', false)
			->with(['hub'])
			->get()
			->map(function($u){
				return $u->hub()
					->with(['manager', 'manager.user'])
					->first();
			});

		// response
		$data = [
			'user' => $user,
			'hubs' => $hubs,
			'pending_hubs' => $pending_hubs,
			'form_action' => 'Master\UserController@update',
			'tabs' => [
				'id' => 'hub-details',
				'items' => [
					'details' => ['label' => 'Details', 'active' => true],
					'hubs' => ['label' => 'Hubs']
				],
			]
		];

		return view('master.user.item', $data);
	}

	/**
	 * PUT /master/users/edit/{user}
	 * ROUTE master::user.update [web.php]
	 *
	 * The master user update action
	 * 
	 * @param Request $request
	 * @param User $user
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $user)
	{
		// validation
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email|unique:user,email',
			'password' => 'min:8',
			'confirm_password' => 'same:password'
		]);

		// update user
		$user->name = $request->name;
		$user->email = $request->email;
		$user->password = $request->password;
		$user->summary = $request->summary;
		$user->is_active = isset($request->is_active) ? true : false;
		$user->save();

		// response
		return redirect()->back()->with('success', 'User successfully been updated.');
	}

	// Section : Helpers

	/**
	 * Action : deactivate
	 * Deactivates User
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionDeactivate(Request $request, $ids = [])
	{
		$count = User::whereIn('id', $ids)->count(); // get count of users
		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count !== count($ids)){
			$response->with('error', 'User Deactivation failed!');
		} else {
			User::whereIn('id', $ids)
				->update([
					'is_active' => false
				]);
			
			$response->with('success', 'User deactivated!');
		}

		// response
		return $response;
	}

	/**
	 * Action : activate
	 * Activates User
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionActivate(Request $request, $ids = [])
	{
		$count = User::whereIn('id', $ids)->count(); // get count of users
		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count !== count($ids)){
			$response->with('error', 'User Activation failed!');
		} else {
			User::whereIn('id', $ids)
				->update([
					'is_active' => true
				]);
			
			$response->with('success', 'User Activated!');
		}

		// response
		return $response;
	}

	/**
	 * Action : Hubs Remove
	 * Removes the Hubs of the user
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionHubsRemove(Request $request, $ids = [])
	{
		$count = User::whereIn('id', $ids)->count(); // get count of users
		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count !== count($ids)){
			$response->with('error', 'User removing from Hubs failed!');
		} else {
			User::whereIn('id', $ids)
				->with([
					'membership'
				])
				->get()
				->map(function($user){
					$user->membership()->delete();
				});

			$response->with('success', 'User removed from Hubs!');
		}

		// response
		return $response;
	}

	/**
	 * Action : Delete
	 * Soft Deletes users
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionDelete(Request $request, $ids = [])
	{
		$count = User::whereIn('id', $ids)->count(); // get count of users
		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count !== count($ids)){
			$response->with('error', 'User failed to delete!');
		} else {
			User::whereIn('id', $ids)->delete();
			$response->with('success', 'User deleted!');
		}
		
		// response
		return $response;
	}
}
