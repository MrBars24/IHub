<?php

// Laravel
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

// 3rd Party
use Faker\Factory as Faker;

class PassportSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$name = 'BodeContagion';

		// passport install
		Artisan::call('passport:install');

		// oauth_client
		Artisan::call('passport:client', [
			'--password' => true, '--name' => $name
		]);

		// explicitly set key
		\DB::table('oauth_clients')
			->where('name', '=', $name)
			->update([
				'secret' => env('CLIENT_SECRET', 'berg709KSLpaqgXS6yQJyBqVeoqz4rBWlNrYzlXc') // my system can't get the CLIENT_SECRET value from .env file
			]);

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  oauth_clients");
		$this->command->getOutput()->writeln("");
	}
}
