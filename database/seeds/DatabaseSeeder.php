<?php // @charset UTF-8

// Laravel
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// get detailed exception errors in local environment
		if(app()->environment() == 'local') {
			try {
				$this->seed();
			} catch(\Exception $e) {
				vd($e->getMessage());
				dd($e->getTraceAsString());
			}
		} else {
			$this->seed();
		}
	}

	protected function seed()
	{
		Model::unguard();

		// reset
		$this->call(DatabasePurger::class);

		// globals
		$this->call(PlatformSeeder::class);
		$this->call(NotificationTypeSeeder::class);

		// data
		$this->call(UserSeeder::class);
		$this->call(HubSeeder::class);
		$this->call(GigSeeder::class);
		$this->call(PostSeeder::class);
		$this->call(ConversationSeeder::class);
		$this->call(PassportSeeder::class);

		// api
		$this->call(ApiSeeder::class);

		Model::reguard();
	}
}
