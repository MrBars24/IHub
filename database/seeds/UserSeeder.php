<?php

// Laravel
use Illuminate\Database\Seeder;

// 3rd Party
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$faker = Faker::create();

		// user: master (satoshi)
		$user = [
			'name' => 'Satoshi Payne',
			'slug' => 'satoshi',
			'email' => 'satoshi@satoshipayne.com',
			'password' => 'master',
			'is_active' => true,
			'is_master' => true,
			'filesystem' => 'satoshi',
		];
		factory(App\User::class, 1)->create($user);

		// user: master (influencerhub)
		$user = [
			'name' => 'Bec Derrington',
			'slug' => 'bec',
			'email' => 'rderrington@influencerhub.com',
			'password' => 'bec',
			'is_active' => true,
			'is_master' => true,
			'filesystem' => 'bec',
		];
		factory(App\User::class, 1)->create($user);

		// user: hub manager (bodecontagion)
		$user = [
			'name' => 'Satoshi Payne',
			'slug' => 'satoshipaynebode',
			'email' => 'satoshi@bodecontagion.com',
			'password' => 'hubmanager',
			'is_active' => true,
			'is_master' => false,
		];
		factory(App\User::class, 1)->create($user);

		// user: hub manager (sourcebottle)
		$user = [
			'name' => 'Bec Derrington',
			'email' => 'rderrington@sourcebottle.com',
			'password' => 'hubmanager',
			'is_active' => true,
			'is_master' => false,
		];
		factory(App\User::class, 1)->create($user);

		// user: influencer (bodecontagion)
		$user = [
			'name' => 'Satoshi Payne',
			'email' => 'satoshi.payne@gmail.com',
			'password' => 'influencer',
			'is_active' => true,
			'is_master' => false,
		];
		factory(App\User::class, 1)->create($user);

		// user
		$count = $faker->numberBetween(20, 25);
		for($i = 0; $i < $count; $i++) {
			$user = [];
			factory(App\User::class, 1)->create($user);
		}

		// linked_account: Satoshi Payne's facebook
		$account = [
			'user_id' => 5, // satoshi.payne@gmail.com
			'is_enabled' => true,
			'platform' => 'facebook',
			'native_id' => '10153549724002488',
			'token' => 'EAAB1RfnS3zgBALrrhs4nc3drjKnHscxei3DW7imr3IZCMdW6GehZBrBpjVmKxwPhaZC9hZAt1tNVTunRrQ1gug3Q97dfhRRuZBFfZBZAU161voOylEJFuZA1NaA09gJt7BxYPwPwcHMWcV2zBZC1P4mYMtsv7U0t8AZCZA1SJQLEkOmo9GpxAoF0ij0',
			'name' => 'Satoshi Payne',
			'followers_label' => 'friends',
			'followers' => 854,
		];
		factory(App\LinkedAccount::class, 1)->create($account);

		// linked_account: Satoshi Payne's twitter
		$account = [
			'user_id' => 5, // satoshi.payne@gmail.com
			'is_enabled' => true,
			'platform' => 'twitter',
			'native_id' => '110866934',
			'token' => '110866934-799cxPhLmNABizQDYlOKvqtEcUsXKVwRoHyrl4lZ',
			'secret' => '82vHtwfcVlgOddN0I7Bi6Rk5jtBx7VinOi39ddEaHYcqg',
			'name' => '@satoshipayne',
			'followers_label' => 'followers',
			'followers' => 15,
		];
		factory(App\LinkedAccount::class, 1)->create($account);

		// linked_account: Satoshi Payne's pinterest
		$account = [
			'user_id' => 5, // satoshi@satoshipayne.com
			'is_enabled' => true,
			'platform' => 'pinterest',
			'native_id' => '449023162756120519',
			'token' => 'ARNxHVEnQs9V8PaASAw1dIqXCmXPFP1b-HRmUClC5OGzciBDMQAAAAA',
			'name' => 'Satoshi Payne',
			'followers_label' => 'followers',
			'followers' => 12,
		];
		factory(App\LinkedAccount::class, 1)->create($account);

		// linked_account: Eric's pinterest
		$account = [
			'user_id' => 3, // satoshi.payne@gmail.com
			'is_enabled' => true,
			'platform' => 'pinterest',
			'native_id' => '509469914004748372',
			'token' => 'AafPaA-2pgjnBwdxQop1t_52uLVyFP0SSmR_QZJEhbi5AKAvbAAAAAA',
			'name' => 'Kaori Locop',
			'followers_label' => 'followers',
			'followers' => 55,
		];
		factory(App\LinkedAccount::class, 1)->create($account);

		// linked_account: Satoshi Payne's youtube
		$account = [
			'user_id' => 5, // satoshi.payne@gmail.com
			'is_enabled' => true,
			'platform' => 'youtube',
			'native_id' => 'UCI6rxbZwTp1M9EZUOm_uOOg',
			'token' => '{"access_token":"ya29.GlsZBVmUv1AmwuacrC47Xt4Ff3Ic-Y6pqgfR4_t6CluUE_elMtiif81xBOQ4t4tpbK_DPHQaAwkO6eo_jBmNwBUGK3SZ7d4BUKtm5DY3Pmni0bCahTME5crCgUM_","expires_in":3600,"refresh_token":"1\/knCDjvBcPpS7zxPBAVsld7n3uhkOk8_rTRDaBlWW5ZI","token_type":"Bearer","created":1512468191}',
			'name' => 'Satoshi Payne',
			'followers_label' => 'subscribers',
			'followers' => 123,
		];
		factory(App\LinkedAccount::class, 1)->create($account);

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  user");
		$this->command->getOutput()->writeln("<info>Table:</info>  linked_account");
		$this->command->getOutput()->writeln("");
	}
}
