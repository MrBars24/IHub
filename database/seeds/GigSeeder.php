<?php

// Laravel
use Illuminate\Database\Seeder;

// 3rd Party
use Faker\Factory as Faker;

class GigSeeder extends Seeder
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
		$hubs = App\Hub::all()->keyBy('slug');
		$platforms = App\Platform::all();
		$categories = App\Category::all();

		// gig
		$arrGigs = [];
		$count = $faker->numberBetween(25, 39);
		for($i = 0; $i < $count; $i++) {
			$gig = [
				'hub_id' => $hubs->random()->id
			];
			$arrGigs[] = factory(App\Gig::class, 1)->create($gig);
		}

		// gig_category
		foreach($arrGigs as $gig) {
			$count = $faker->numberBetween(2, 5);
			$subset = $platforms->random($count);
			$gig->platforms()->attach($subset->pluck('id')->toArray());
		}

		// gig_platform
		foreach($arrGigs as $gig) {
			$count = $faker->numberBetween(2, 5);
			$subset = $categories->random($count);
			$gig->categories()->attach($subset->pluck('id')->toArray());
		}

		// gig_attachment
		$links = collect([
			'https://www.youtube.com/watch?v=M4qh3j5A4SQ',
			'https://www.youtube.com/watch?v=4SSVDU1VhPk',
			'https://www.youtube.com/watch?v=d8s8e8JdGCc',
			'https://www.youtube.com/watch?v=kdMnlW4_3Nc',
			'https://www.youtube.com/watch?v=HBEbp9X4wf4',
			'https://www.youtube.com/watch?v=pAWduxoCgVk',
			'https://www.youtube.com/watch?v=9h3lByx59ns',
			'https://vimeo.com/42438167',
			'https://vimeo.com/46960140',
			'https://vimeo.com/16440875',
			'https://vimeo.com/162826208',
			'https://laracasts.com/discuss/channels/requests/laravel-rss-reader',
			'https://gitlab.com/',
			'https://www.meetup.com/'
		]);
		foreach($arrGigs as $gig) {
			$link = $links->random();
			$type = 'link';
			switch(true) {
				case strpos($link, 'youtube.com') != false:
					$type = 'youtube';
					break;
				case strpos($link, 'vimeo.com') != false:
					$type = 'vimeo';
					break;
			}
			$attachment = [
				'hub_id' => $gig->hub_id,
				'gig_id' => $gig->id,
				'type' => $type,
				'url' => $link
			];
			//factory(App\GigAttachment::class, 1)->create($attachment);
		}

		// reward
		foreach($arrGigs as $gig) {
			$count = $faker->numberBetween(2, 6);
			for($i = 0; $i < $count; $i++) {
				$reward = [
					'gig_id' => $gig->id
				];
				factory(App\Reward::class, 1)->create($reward);
			}
		}

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  gig");
		$this->command->getOutput()->writeln("<info>Table:</info>  gig_category");
		$this->command->getOutput()->writeln("<info>Table:</info>  gig_platform");
		$this->command->getOutput()->writeln("<info>Table:</info>  gig_attachment");
		$this->command->getOutput()->writeln("<info>Table:</info>  reward");
		$this->command->getOutput()->writeln("");
	}
}
