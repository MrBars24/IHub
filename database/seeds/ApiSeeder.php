<?php

// Laravel
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

// 3rd Party
use Faker\Factory as Faker;

class ApiSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// facebook_page_access, pinterest_board_access
		Artisan::call('social:cache-pages');

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  facebook_page_access");
		$this->command->getOutput()->writeln("<info>Table:</info>  pinterest_board_access");
		$this->command->getOutput()->writeln("");
	}
}
