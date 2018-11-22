<?php
namespace App\Http\Controllers\Hub;

// App
use App\Http\Controllers\Controller;
use App\Hub;
use App\Category;
use App\Platform;
use App\NotificationType;
use App\User;
use App\Membership;
use App\MembershipGroup;
use App\NotificationSetting;
use App\AlertCategorySetting;
use App\FileStorage;
use App\LinkedAccount;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\CheckSlugRequest;
use App\Modules\Files\FileManager;
use App\Events\Terms\UserTerms;

// Laravel
use Illuminate\Http\Request;
use Carbon\Carbon;

// 3rd part
use Image;

class SettingsController extends Controller
{
	/**
	 * GET /api/{hub}/settings/{tab?}
	 * ROUTE hub::settings [api.php]
	 *
	 * Load the settings page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  string                    $tab
	 * @return \Illuminate\Http\Response
	 */
	public function getSettings(Request $request, Hub $hub, $tab = 'account')
	{
		// load information from correct tab
		$settings = null;
		switch($tab) {
			case 'account':
				$settings = $this->getAccount($request, $hub);
				break;
			case 'alerts':
				$settings = $this->getAlerts($request, $hub);
				break;
			case 'messages':
				$settings = $this->getMessages($request, $hub);
				break;
			case 'profile':
				$settings = $this->getProfile($request, $hub);
				break;
			case 'community':
				$settings = $this->getCommunity($request, $hub);
				break;
			case 'influencer':
				$settings = $this->getInfluencers($request, $hub);
				break;
		}

		// response
		$data = [
			'data' => [
				'settings' => $settings
			],
			'route' => 'hub::settings', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/settings/{tab?}
	 * ROUTE hub::settings [api.php]
	 *
	 * Process the settings page
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  string                    $tab
	 * @return \Illuminate\Http\Response
	 */
	public function postSettings(Request $request, Hub $hub, $tab = 'account')
	{
		// load information from correct tab
		$settings = null;
		switch($tab) {
			case 'account':
				$settings = $this->postAccount($request, $hub);
				break;
			case 'alerts':
				$settings = $this->postAlerts($request, $hub);
				break;
			case 'messages':
				$settings = $this->postMessages($request, $hub);
				break;
			case 'profile':
				$settings = $this->postProfile($request, $hub);
				break;
			case 'community':
				$settings = $this->postCommunity($request, $hub);
				break;
			case 'influencer':
				$settings = $this->postInfluencers($request, $hub);
				break;
		}

		// response
		$data = [
			'data' => [
				'settings' => $settings
			],
			'route' => 'hub::settings', 
			'success' => true
		];
		return response()->json($data);
	}

	/// Section: Account

	/**
	 * Get details for the account tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getAccount(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		$data = [
			'email' => $auth_user->email,
			'accounts' => $auth_user->accounts,
		];
		return $data;
	}

	/**
	 * Set details for the account tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function postAccount(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// data object
		$data = [];

		// update password if changed
		if($request->has('password') && $request->has('password_re')) {
			if($request->input('password') === $request->input('password_re')) {
				$auth_user->password = $request->input('password');
				$auth_user->save();
				$data['password'] = 'Password has changed.';
			}
		}

		// update email if changed
		if($request->input('email') !== $auth_user->email) {
			$auth_user->email = $request->input('email');
			$auth_user->save();
			$data['email'] = 'Email address has changed.';
		}
		return $data;
	}

	/// Section: Alerts

	/**
	 * Get details for the alerts tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getAlerts(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get membership
		$membership = $auth_user->getMembershipTo($hub);

		// get membership categories and platforms
		$platforms = Platform::query()
			->leftJoin('alert_platform_setting', function($join) use ($membership) {
				$join->on('platform.id', '=', 'alert_platform_setting.platform_id')
					->where('alert_platform_setting.membership_id', '=', $membership->id);
			})
			->select(['alert_platform_setting.*', 'platform.name', 'platform.id'])
			->get();
		$membership->setRelation('platforms', $platforms);

		// get list of categories and join on the member's settings
		$categories = Category::query()
			->leftJoin('alert_category_setting', function($join) use ($membership) {
				$join->on('category.id', '=', 'alert_category_setting.category_id')
					->where('alert_category_setting.membership_id', '=', $membership->id);
			})
			->where('category.hub_id', '=', $hub->id)
			->select(['alert_category_setting.*', 'category.name', 'category.id'])
			->orderBy('category.name', 'ASC')
			->get();
		$membership->setRelation('categories', $categories);

		$data = [
			'email' => $auth_user->email,
			'membership' => $membership,
		];
		return $data;
	}

	/**
	 * Set details for the alerts tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function postAlerts(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get params
		$settings = $request->input('membership');

		// get membership
		$membership = $auth_user->getMembershipTo($hub);

		// update categories
		$categories = [];
		if(isset($settings['categories'])) {
			foreach($settings['categories'] as $category) {
				$categories[$category['id']] = ['is_selected' => $category['pivot']['is_selected']];
			}
			unset($settings['categories']);
			$membership->categories()->syncWithoutDetaching($categories);
		}

		// update platforms
		$platforms = [];
		if(isset($settings['platforms'])) {
			foreach($settings['platforms'] as $platform) {
				$platforms[$platform['id']] = ['is_selected' => $platform['pivot']['is_selected']];
			}
			unset($settings['platforms']);
			$membership->platforms()->syncWithoutDetaching($platforms);
		}

		// update main membership data
		$membership->update($settings);

		return [
			'alerts' => 'Gig alert preferences changed.'
		];
	}

	/// Section: Messages

	/**
	 * Get details for the messages tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getMessages(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get membership settings
		$membership = $auth_user->getMembershipTo($hub);
		$enabled = ['all', $membership->role];
			
		$settings = NotificationType::query()
			->leftJoin('notification_setting', function($join) use ($membership) {
				$join->on('notification_setting.type_id', '=', 'notification_type.id')
					->where('notification_setting.membership_id', '=', $membership->id);
			})
			->whereIn('notification_type.enabled_for', $enabled)
			->select([
				'notification_setting.send_web',
				'notification_setting.send_email',
				'notification_setting.send_push',
				'notification_type.key',
				'notification_type.label',
				'notification_type.id as type_id',
			])
			->get();

		return [
			'notification_settings' => $settings,
			'receive_push_notifications' => $auth_user->receive_push_notifications
		];
	}

	/**
	 * Set details for the messages tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function postMessages(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// update receive_push_notifications
		$auth_user->receive_push_notifications = $request->input('receive_push_notifications');
		$auth_user->save();
		
		$settingsRequest = collect($request->input('notification_settings'));
		$membership = $auth_user->getMembershipTo($hub);
		
		$settings = NotificationSetting::query()
			->where('membership_id', $membership->id)
			->get()
			->keyBy('type_id');

		// update settings
		foreach($settingsRequest as $values) {
			$settingData = [
				'send_web' => $values['send_web'],
				'send_push' => $values['send_push'],
				'send_email' => $values['send_email']
			];

			// existing
			if (isset($settings[$values['type_id']])) {
				$setting = $settings[$values['type_id']];
				$setting->update($settingData);
			}
			else {
				NotificationSetting::create(array_merge($settingData, [
					'membership_id' => $membership->id,
					'type_id' => $values['type_id']
				]));
			}
		}

		return [
			'messages' => 'Notification settings changed.'
		];
	}

	/// Section: Profile

	/**
	 * Get details for the profile tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getProfile(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get entity
		$entity = $auth_user;
		if($auth_user->getMembershipTo($hub)->role === 'hubmanager') {
			$entity = $hub;
		}

		$entity->makeVisible([
			'id',
			'summary',
			'profile_picture',
			'cover_picture',
			'profile_picture_cropping',
			'cover_picture_cropping',
			'cover_picture_web_path',
			'profile_picture_medium',
			'original_cover_picture_web_path',
			'original_profile_picture_web_path'
		]);

		return $entity->toArray();
	}

	/**
	 * Set details for the profile tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function postProfile(Request $request, Hub $hub)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		
		// data object
		$data = [];

		// get entity
		$entity = $auth_user;
		if($auth_user->getMembershipTo($hub)->role === 'hubmanager') {
			$entity = $hub;

			// update the app branding
			$entity->branding_header_colour = $request->input('branding_header_colour');
			$entity->branding_header_colour_gradient = $request->input('branding_header_colour_gradient');
			$entity->branding_primary_button = $request->input('branding_primary_button');
			$entity->branding_primary_button_text = $request->input('branding_primary_button_text');
			
			$headerLogo = $request->input('branding_header_logo', null);
			$emailLogo = $request->input('email_logo', null);

			// store file if branding header logo is not null and it doesn't match the one in the database
			if(!is_null($headerLogo) && $headerLogo != $entity->branding_header_logo) {
				// store file
				$filename = $entity->storeFile($headerLogo);
				$entity->branding_header_logo = $filename;
			}
			
			// update the email template
			$entity->email_header_colour = $request->input('email_header_colour');
			$entity->email_footer_colour = $request->input('email_footer_colour');
			$entity->email_footer_text_1 = $request->input('email_footer_text_1');
			$entity->email_footer_text_2 = $request->input('email_footer_text_2');
			// store file if email logo is not null and it doesn't match the one in the database
			if(!is_null($emailLogo) && $emailLogo != $entity->email_logo) {
				// store file
				$filename = $entity->storeFile($emailLogo);
				$entity->email_logo = $filename;
			}
		}

		// store images
		$cover_picture = $request->input('cover_picture', null);
		$profile_picture = $request->input('profile_picture', null);
		$cover_picture_cropping = $request->input('cover_picture_cropping', null);
		
		// store file if profile picture is not null and it doesn't match the one in the database
		if(!is_null($profile_picture) && $profile_picture != $entity->profile_picture) {
			$filename = $entity->storeFile($profile_picture);
			$entity->profile_picture = $filename;
		}
		
		// store file if cover picture is not null and it doesn't match the one in the database
		if(!is_null($cover_picture) && $cover_picture != $entity->cover_picture) {
			$filename = $entity->storeFile($cover_picture);
			$entity->cover_picture = $filename;
		}

		// crop file if cover picture is not null and it doesn't match the one in the database
		if(!is_null($cover_picture_cropping) && $cover_picture_cropping != $entity->cover_picture_cropping) {
			$entity->cropFile($entity->cover_picture, $cover_picture_cropping);
		}

		// update profile for entity
		$entity->name = $request->input('name');
		$entity->summary = $request->input('summary');
		$entity->profile_picture_cropping = $request->input('profile_picture_cropping');
		$entity->cover_picture_cropping = $request->input('cover_picture_cropping');
		$entity->profile_picture_display = $request->input('profile_picture_display');
		$entity->save();
		$data['profile'] = 'Profile changed.';

		return $data;
	}

	/// Section: Community
	/**
	 * DELETE /api/{hub}/settings/delete/category
	 * hub::settings.delete.category
	 */
	public function deleteCategory(Request $request, Hub $hub, Category $category)
	{
		// or just
		// $category->delete();
		$category = Category::where('hub_id', $hub->id)
			->where('id', $category->id)
			->delete();

		$data = [
			'data' => [
				'category' => $category
			],
			'route' => 'hub::settings.delete.category', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * Get details for the community tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getCommunity(Request $request, Hub $hub)
	{
		$categories = Category::where('hub_id', $hub->id)
						->where('is_active', true)
						->orderBy('name', 'ASC')
						->get();
		$data = [
			'community_conditions' => $hub->community_conditions,
			'default_gig_conditions' => $hub->default_gig_conditions,
			'email_invite_text' => $hub->email_invite_text,
			'default_gig_require_approval' => $hub->default_gig_require_approval,
			'sharing_meta_linkedin' => $hub->sharing_meta_linkedin,
			'categories' => $categories->toArray(),
		];
		return $data;
	}

	/**
	 * post community settings
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function postCommunity(Request $request, Hub $hub)
	{
		// $hub = Hub::find($hub->id); // create new instance because it's causing an Unknown column 'object_class' Error
		$categories = $request->input('categories');
		$data['community'] = 'Community settings has changed.';

		
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$membership = $auth_user->membership;

		$categoryCollection = [];

		foreach($categories as $key => $_category) {
			if (isset($_category['id'])) {
				// old category
				$category = Category::find($_category['id']);

				if ($_category['removing']) { // delete
					$category->delete();
				}
				else { // update
					$category->update($_category);
				}
			}
			else { // new category, create new category, fill membership categories as well.
				$category = new Category;
				$category->fill($_category);
				$category->hub_id = $hub->id;
				$category->is_active = true;
				$category->save();

				array_push($categoryCollection, $category->id);
			}
		}

		// fill membership categories alert settings
		$membership->categories()->syncWithoutDetaching($categoryCollection);
		
		$forceReaccept = $request->input('force_reaccept');
		$hub->community_conditions = $request->input('community_conditions');
		$hub->default_gig_conditions = $request->input('default_gig_conditions');
		$hub->default_gig_require_approval = $request->input('default_gig_require_approval');
		$hub->sharing_meta_linkedin = $request->input('sharing_meta_linkedin', $hub->name);
		$hub->email_invite_text = $request->input('email_invite_text');
		
		if ($forceReaccept && $request->input('community_conditions')) {
			$hub->conditions_updated_at = carbon();

			if($hub->getOriginal('community_conditions') != $hub->getAttribute('community_conditions')) {
				$hub->members()->update(['accepted_conditions' => 0]);
			}
			
			broadcast(new UserTerms($hub, $hub->members()->pluck('user_id')));
		}
		$hub->save();
		return $data;
	}

	/// Section: Influencers
	/**
	 * Get details for the influencer tab
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return array
	 */
	private function getInfluencers(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$data = [
			'custom_fields' => $hub->custom_fields
		];
		return $data;
	}

	/**
	 * 
	 */
	private function postInfluencers(Request $request, Hub $hub)
	{
		$data = [];
		$fields = $request->input('custom_fields');
		$newFields = [];
		foreach($fields as $field) {
			// delete from the custom fields
			if ($field['deleted'] || !$field['name']) {
				unset($field);
			}
			else {
				array_push($newFields, $field['name']);
			}
		}
		$hub->custom_fields = $newFields; // automatically casted into json array
		$hub->save(); 
		// NOTE: should we update the membership->custom_fields too ??

		$data['influencers'] = 'Custom fields updated.';
		return $data;
	}

	/**
	 * TODO: think of a better name for this,
	 * this is for the membership influencer list
	 * GET /api/{hub}/settings/influencers
	 * ROUTE hub::settings.influencers [api.php]
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function getInfluencersList(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$influencers = Membership::with(['user', 'groups' => function($qry) {
				$qry->select(['membership_group.name', 'membership_group.id']);
			}])
			->where('role', '=', 'influencer')
			->where('hub_id', '=', $hub->id)
			->orderBy('is_active', 'DESC')
			->paginate(30);

		// response
		$data = [
			'data' => [
				'influencers' => $influencers->toArray()
			],
			'route' => 'hub::settings.influencers', 
			'success' => true
		];
		return response()->json($data);
	}

	public function exportInfluencers(Hub $hub)
	{
		$influencers = Membership::query()
			->leftJoin('user', 'membership.user_id', '=', 'user.id')
			->select([
				'user.name AS name',
				'user.email AS email',
				'user.is_active AS user_active',
				'membership.is_active AS membership_active',
				'membership.custom_fields AS custom_fields'
			])
			->where('membership.role', '=', 'influencer')
			->where('membership.hub_id', '=', $hub->id)
			->where('membership.is_active', '=', true)
			->whereIn('membership.status', ['pending', 'member']) // pending and active?
			->whereNull('membership.deleted_at') // not deleted
			->where('user.is_active', true) // ???
			->get()
			->toArray();

		// get columns
		$csv = [];
		$additionalColumns = !is_null($hub->custom_fields) ? 
							$hub->custom_fields : [];;
		$columns = [
			'name' => 'Name',
			'email' => 'Email Address',
			'user_active' => 'User Active',
			'membership_active' => 'Member Active'
		] + $additionalColumns;

		// build csv data
		$csv[] = $columns;

		foreach($influencers as $member) {
			$row = [];
			foreach($columns as $col => $label) {
				// @satoshi, hmm what part of the app do we update the custom_fields of the membership?
				$value = isset($member[$col]) ? $member[$col] : (isset($member['custom_fields'][$col]) ? $member['custom_fields'][$col] : '');
				$row[$col] = str_replace('"', '""', $value);
			}
			$csv[] = $row;
		}

		// build output
		$output = '';
		foreach($csv as $row) {
			$output .= '"' . implode('","', $row) . '"' . PHP_EOL;
		}

		// response
		$filename = 'influencers-'.date('YmdHis').'.csv';
		$headers = [
			'Content-Type' => 'text/csv',
			'Content-Disposition' => "attachment; filename=".$filename,
		];
		return response()->make($output, 200, $headers);


	}

	/**
	 * [removeInfluencers description]
	 * ROUTE hub::settings.influencers.remove
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 */
	public function removeInfluencers(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$influencer_ids = $request->input('influencer_ids');
		$memberships = Membership::where('hub_id', $hub->id)
					->whereIn('user_id', $influencer_ids)
					->update([
						'is_active' => false,
						'deleted_at' => carbon()
						// 'status' => 'pending' or suspended?
					]);
		
		// response
		$data = [
			'data' => [
				'memberships' => $memberships
			],
			'route' => 'settings.influencers.remove', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST hub::settings.reset.points
	 * 
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function resetPoints(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$influencer_ids = $request->input('influencer_ids');
		$memberships = Membership::where('hub_id', $hub->id)
					->where('is_active', true)
					->whereIn('user_id', $influencer_ids)
					->get();
		foreach($memberships as $membership) {
			// TODO: error when resetting the points.
			$membership->resetPoints('manualreset');
		}

		// response
		$data = [
			'data' => [
				'influencer_ids' => $influencer_ids
			],
			'route' => 'hub::settings.reset.points', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	* POST hub::settings.influencers.invite
	* expects an array from the import | email
	* @param  Request $request	expects array from csv file | email
	* @param  Hub     $hub     [description]
	* @return [type]           [description]
	*/
	public function inviteInfluencers(Request $request, Hub $hub)
	{
		$payload = $request->input('payload'); // dynamic payload
		$user = null;
		$users = [];
		$inviteType = 'solo'; // or bulk

		if (gettype($payload) === 'string') {
			$user = User::inviteByEmail($payload, $hub);
		}
		else if (gettype($payload) === 'array') {
			$inviteType = 'bulk';
			$users = User::inviteListByEmail($payload, $hub);
		}

		// response
		$data = [
			'data' => [
				'invited'  => $inviteType == 'solo' ? $user : $users
			],
			'route' => 'hub::settings.influencers.invite', 
			'success' => $inviteType == 'solo' ? boolval($user) : true
		];
		return response()->json($data);
	}

	/**
	 * POST hub::settings.membership.groups.create
	 * 
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function postMembershipGroups(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$membershipGroup = new MembershipGroup;
		$membershipGroup->fill($request->all());
		$membershipGroup->hub_id = $hub->id;
		$membershipGroup->save();
		
		// response
		$data = [
			'data' => [
				'membership_group' => $membershipGroup
			],
			'route' => 'hub::settings.membership.groups.create', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/settings/membership/groups/set
	 * ROUTE hub::settings.membership.groups.set [api.php]
	 */
	public function setGroup(Request $request, Hub $hub)
	{
		// get group, or empty selection
		$group = $request->input('group_id');
		if(strlen($group) > 0) {
			$group = [$group];
		} else {
			$group = [];
		}

		// get membership
		$membership = Membership::query()
			->where('id', '=', $request->input('membership_id'))
			->where('hub_id', '=', $hub->id)
			->first();

		// save group selection for membership
		$membership->groups()->sync($group);

		// response
		$data = [
			'data' => [
				'membership' => $membership
			],
			'route' => 'hub::settings.membership.groups.set', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET hub::settings.membership.groups
	 * 
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function getMembershipGroups(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$membershipGroup = $hub->membershipGroups;
		// response
		$data = [
			'data' => [
				'membership_groups' => $membershipGroup->toArray()
			],
			'route' => 'hub::settings.membership.groups', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET hub::settings.membership.groups.delete
	 * 
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function deleteMembershipGroup(Request $request, Hub $hub, MembershipGroup $group)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$group->delete();
		// response
		$data = [
			'data' => [
				'membership_group' => $group
			],
			'route' => 'hub::settings.membership.groups.delete', 
			'success' => true
		];
		return response()->json($data);
	} 

	/**
	 * GET hub::settings.custom-fields.update
	 * 
	 * @param  Request $request [description]
	 * @param  Hub     $hub     [description]
	 * @return [type]           [description]
	 */
	public function getCustomFields(Request $request, Hub $hub)
	{
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// response
		$data = [
			'data' => [

			],
			'route' => 'hub::settings.custom-fields.update', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /{hub}/settings/linked-account
	 * ROUTE hub::settings.linked-account [api.php]
	 */
	public function updateLinkedAccounts(Request $request, Hub $hub)
	{
		$user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		$account_ids = $request->input('account_ids', []);
		$account = LinkedAccount::whereIn('id', $account_ids)->delete();
		
		// response
		$data = [
			'data' => [
				'account' => $account
			],
			'route' => 'hub::settings.linked-account', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /account-setup
	 * ROUTE hub::account-setup [web.php]
	 * @param  Hub        $hub        [description]
	 * @param  Membership $membership [description]
	 * @return [type]                 [description]
	 */
	public function accountSetup(Hub $hub, Membership $membership)
	{
		// NOTE: all javascript routes redirect must be hardcoded.
		$membership->load('user');

		$auth_user = session_user();
		// find the current token being used.
		if (!is_null($auth_user)) {
			$auth_user->oauth_tokens()->delete();
		}

		// logout current user 

		// check if setup is required
		// check if membership is already active
		if($membership->isActive()) {
			// redirect to lndex
			return redirect("/$hub->slug");
		}

		// if the user is already a member or using the Influencer Hub. 
		// activate the membership
		if($membership->user->is_active && !$membership->isActive()) {
			$membership->status = 'member';
			$membership->is_active = true;
			$membership->save();
			return redirect("/$hub->slug");
		}

		// password and account already setup - redirect to login
		if($membership->user->is_active == true) {
			// redirect to login
			return redirect('/login');
		}

		// redirect to /signup to setup their name and password
		$email = $membership->user->email;
		return redirect("/account-setup/?membership=$membership->id&hub=$hub->slug&email=$email");
	}

	/**
	 * POST /account-submit
	 * ROUTE hub::account-submit [api.php]
	 * @param  Request    $request    [description]
	 * @param  Hub        $hub        [description]
	 * @param  Membership $membership [description]
	 * @return [type]                 [description]
	 */
	public function accountSubmit(RegisterRequest $request, Hub $hub, Membership $membership)
	{
		// get user
		$user = $membership->user;

		// update user
		// detect profile picture
		if($request->has('profile_picture')) {
			// take remote image
			$image = Image::make($request->input('profile_picture'));
			// stage file
			$staged = app(FileManager::class)->stage($image);
			// store and move the file
			$filename = $user->storeFile($staged['path']);
			$user->profile_picture = $filename;
		}

		$user->is_active = true;
		$user->password = $request->input('password');
		$user->name = $request->input('name');
		$user->slug = $request->input('slug');
		$user->save();

		// update membership
		$membership->status = 'member';
		$membership->is_active = true;
		$membership->save();

		// response
		$data = [
			'data' => [
				'message' => 'You can now login.',
			],
			'route' => 'hub::account-submit', 
			'success' => true
		];
		return response()->json($data);
	}

	public function acceptTerms(Request $request, Hub $hub) {
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get membership
		$membership = $auth_user->getMembershipTo($hub);

		$membership->accepted_conditions = true;
		$membership->accepted_conditions_at = new Carbon;
		$membership->save();

		// response
		$data = [
			'success' => true
		];

		return response()->json($data);
	}
}
