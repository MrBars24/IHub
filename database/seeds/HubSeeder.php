<?php

// Laravel
use Illuminate\Database\Seeder;

// 3rd Party
use Faker\Factory as Faker;

class HubSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$faker = Faker::create();

		// dependencies
		$users = App\User::where('is_master', '=', false)->get()->keyBy('email');
		$notificationTypes = App\NotificationType::all()->keyBy('key');
		$platforms = App\Platform::all()->keyBy('platform');

		// hub
		$arrHubs = [];
		$hubs = [
			['name' => 'Bode Contagion', 'slug' => 'bodecontagion', 'filesystem' => 'bodecontagion'],
			['name' => 'SourceBottle', 'slug' => 'sourcebottle', 'filesystem' => 'sourcebottle'],
			['name' => 'Carlton & United Breweries', 'slug' => 'cub', 'filesystem' => 'cub',
				'branding_header_colour' => '#3a348c',
				'branding_header_colour_gradient' => '#ffffff',
				'branding_primary_button' => '#585858',
				'branding_primary_button_text' => '#ffffff'],
			['name' => 'Foxtel', 'slug' => 'foxtel', 'filesystem' => 'foxtel',
				'summary' => 'We love TV as much as you do.',
				'branding_header_colour' => '#ff4f4f',
				'branding_header_colour_gradient' => '#2d211f',
				'branding_primary_button' => '#2d211f',
				'branding_primary_button_text' => '#ffffff'],
		];
		foreach($hubs as $i => $hub) {
			$arrHubs[$hub['slug']] = factory(App\Hub::class, 1)->create($hub);
		}

		// category
		$arrCategories = [];
		foreach($arrHubs as $hub) {
			$count = $faker->numberBetween(3, 9);
			for($i = 0; $i < $count; $i++) {
				$data = array(
					'hub_id' => $hub->id,
				);
				$arrCategories[] = factory(App\Category::class)->create($data);
			}
		}

		// membership
		$arrMemberships = [];
		$memberships = [
			// 1
			['hub' => 'bodecontagion', 'email' => 'satoshi@bodecontagion.com', 'role' => 'hubmanager'],
			['hub' => 'bodecontagion', 'email' => 'satoshi.payne@gmail.com', 'role' => 'influencer'],
			// 2
			['hub' => 'sourcebottle', 'email' => 'rderrington@sourcebottle.com', 'role' => 'hubmanager'],
			// 3
			['hub' => 'cub', 'email' => 'rderrington@sourcebottle.com', 'role' => 'hubmanager'],
			['hub' => 'cub', 'email' => 'satoshi.payne@gmail.com', 'role' => 'influencer'],
			// 4
			['hub' => 'foxtel', 'email' => 'rderrington@sourcebottle.com', 'role' => 'hubmanager'],
			['hub' => 'foxtel', 'email' => 'satoshi.payne@gmail.com', 'role' => 'influencer'],
		];
		$exclude = [];
		foreach($memberships as $i => $membership) {
			$key = $membership['email'];
			$hub = $arrHubs[$membership['hub']];
			$membership['user_id'] = $users[$membership['email']]->id;
			$membership['hub_id'] = $hub->id;
			unset($membership['hub']);
			unset($membership['email']);
			$arrMemberships[] = factory(App\Membership::class, 1)->create($membership);

			// remove users here so they won't be selected again for membership below
			$exclude[] = $key;

			// set hub email address
			if($membership['role'] == 'hubmanager') {
				$hub->email = $key;
				$hub->save();
			}
		}
		foreach($exclude as $key) {
			unset($users[$key]);
		}

		// membership: influencer
		foreach($users as $i => $user) {
			$membership = [
				'hub_id' => collect($arrHubs)->random()->id,
				'user_id' => $user->id,
				'role' => 'influencer',
			];
			$arrMemberships[] = factory(App\Membership::class, 1)->create($membership);
		}

		// @depreceated. we moved this in the Membership->created event.
		// alert_category_setting
		/*foreach($arrMemberships as $membership) {
			foreach($arrCategories as $key => $category) {
				$setting = [
					'membership_id' => $membership->id,
					'category_id' => $category->id,
				];
				factory(App\AlertCategorySetting::class, 1)->create($setting);
			}
		}

		// alert_platform_setting
		foreach($arrMemberships as $membership) {
			foreach($platforms as $key => $platform) {
				$setting = [
					'membership_id' => $membership->id,
					'platform_id' => $platform->id,
				];
				factory(App\AlertPlatformSetting::class, 1)->create($setting);
			}
		}

		// notification_setting
		foreach($arrMemberships as $membership) {
			foreach($notificationTypes as $key => $type) {
				$setting = [
					'membership_id' => $membership->id,
					'type_id' => $type->id,
					'send_web' => $type->send_web,
					'send_email' => $type->send_email,
					'send_push' => $type->send_push,
				];
				factory(App\NotificationSetting::class, 1)->create($setting);
			}
		}*/

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  hub");
		$this->command->getOutput()->writeln("<info>Table:</info>  membership");
		$this->command->getOutput()->writeln("<info>Table:</info>  category");
		$this->command->getOutput()->writeln("<info>Table:</info>  alert_category_setting");
		$this->command->getOutput()->writeln("<info>Table:</info>  alert_platform_setting");
		$this->command->getOutput()->writeln("<info>Table:</info>  notification_setting");
		$this->command->getOutput()->writeln("");
	}
}
