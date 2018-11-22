<?php

namespace App\Http\Controllers\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

// Models
use App\User;

// Traits
use App\Traits\ListAction;

class StaffController extends Controller
{
	use ListAction;

	/// Section: Properties

	protected $valid_actions = [
		'deactivate', 'activate', 'delete', 'user_demote'
	];

	/**
	 * GET /master/staffs
	 * ROUTE master::staff.index [web.php]
	 *
	 * The master staff index page
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		// get master users
		$staffs = User::where("is_master", "=", true)
			->paginate(50);

		$staffs->each(function($item) {
			// created_at
			$item->created_at_formatted = $item->created_at ? $item->created_at->format('dS M Y') : null;
		});

		// response
		$data = [
			'staffs' => $staffs
		];

		return view('master.staff.index', $data);
	}

	/**
	 * GET /master/staffs/create
	 * ROUTE master::staff.create [web.php]
	 *
	 * The master staff create page
	 * 
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		// response
		$data = [
			'user' => new User,
			'form_action' => 'Master\StaffController@store'
		];
		
		return view('master.staff.create', $data);
	}

	/**
	 * POST /master/staffs/create
	 * ROUTE master::staff.store [web.php]
	 *
	 * The master staff store action
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

		// save staff
		$req = $request->all();
		$req['is_master'] = true; // set user as master

		User::create($req);

		// response
		return redirect()->back()->with('success','Staff successfully been created.');
	}

	/**
	 * GET /master/staffs/edit
	 * ROUTE master::staff.edit [web.php]
	 *
	 * The master staff edit page
	 * 
	 * @param Request $request
	 * @param User $staff
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, User $staff)
	{
		// response
		$data = [
			'user' => $staff,
			'form_action' => 'Master\StaffController@update'
		];
		
		return view('master.staff.item', $data);
	}

	/**
	 * PUT /master/staffs/edit
	 * ROUTE master::staff.edit [web.php]
	 *
	 * The master staff update action
	 * 
	 * @param Request $request
	 * @param User $staff
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, User $staff)
	{
		// validation
		$this->validate($request, [
			'name' => 'required|max:255',
			'email' => 'required|email',
			'password' => 'min:8',
			'confirm_password' => 'same:password'
		]);

		// save staff
		$staff->name = $request->name;
		$staff->email = $request->email;
		$staff->password = $request->password;
		$staff->summary = $request->summary;
		$staff->is_active = isset($request->is_active) ? true : false;
		$staff->save();

		// response
		return redirect()->back()->with('success','Staff successfully been updated.');
	}

	// Section : Helpers

	/**
	 * Action : Deactivate
	 * Deactivate staff
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionDeactivate(Request $request, $ids = [])
	{
		// get count
		$count = User::whereIn('id', $ids)
			->where('is_master', '=', true)
			->where('is_active', '=', true)
			->count(); // get count of users

		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count !== count($ids)){
			$response->with('error','Staff Deactivation failed!');
		} else {
			User::whereIn('id', $ids)
				->where('is_master', '=', true)
				->where('is_active', '=', true)
				->update([
					'is_active' => false
				]);
			
			$response->with('success','Staff deactivated!');
		}

		// response
		return $response;
	}

	/**
	 * Action : User Demote
	 * Demote staff to user
	 *
	 * @param Request $request
	 * @param array $ids
	 * @return \Illuminate\Http\RedirectResponse
	 */
	private function actionUserDemote(Request $request, $ids = [])
	{
		// get count
		$count = User::whereIn('id', $ids)
			->where('is_master', '=', true)
			->where('is_super_admin', '=', false)
			->count(); // get count of users

		$response = redirect()->back();

		// check if count from database match with the count of ids passed
		if($count <= 0) {
			$response->with('error','Staff Demotion failed!');
		} else {
			User::whereIn('id', $ids)
				->where('is_master', '=', true)
				->where('is_super_admin', '=', false)
				->update([
					'is_master' => false
				]);
			
			$response->with('success','Staff demoted to user!');
		}

		// response
		return $response;
	}
}
