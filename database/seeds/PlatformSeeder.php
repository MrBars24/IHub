<?php

// Laravel
use Illuminate\Database\Seeder;

// 3rd Party
use Faker\Factory as Faker;

class PlatformSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// platform
		$platforms = array(
			'facebook'  => 'Facebook',
			'twitter'   => 'Twitter',
			'linkedin'  => 'LinkedIn',
			'pinterest' => 'Pinterest',
			'youtube'   => 'YouTube',
			'instagram' => 'Instagram',
		);
		foreach($platforms as $key => $value) {
			$data = array(
				'name' => $value,
				'platform' => strtolower($value),
				'is_active' => true,
			);
			factory(App\Platform::class)->create($data);
		}

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  platform");
		$this->command->getOutput()->writeln("");
	}
}
