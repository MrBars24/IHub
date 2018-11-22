<?php

// Laravel
use Illuminate\Database\Seeder;

// 3rd Party
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$faker = Faker::create();
		$fm = app(\App\Modules\Files\FileManager::class);

		// dependencies
		$hubs = App\Hub::all()->keyBy('slug');
		$users = App\User::join('membership', 'membership.user_id', '=', 'user.id')->where('is_master', '=', false)->get()->groupBy('hub_id');

		// post
		$arrPosts = [];
		$count = $faker->numberBetween(18, 27);
		for($i = 0; $i < $count; $i++) {
			$hub_id = $hubs->random()->id;
			$post = [
				'hub_id' => $hub_id,
				'author_id' => $users[$hub_id]->random()->user_id,
				'author_type' => \App\User::class
			];
			$arrPosts[] = factory(App\Post::class, 1)->create($post);
		}

		// post_attachment
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
		foreach($arrPosts as $post) {
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
				'hub_id' => $post->hub_id,
				'post_id' => $post->id,
				'type' => $type,
				'url' => $link
			];
			factory(App\PostAttachment::class, 1)->create($attachment);
		}

		// comment
		$arrComments = [];
		foreach($arrPosts as $post) {
			$count = $faker->numberBetween(2, 5);
			for($i = 0; $i < $count; $i++) {
				$comment = [
					'hub_id' => $post->hub_id,
					'post_id' => $post->id,
					'author_id' => $users[$post->hub_id]->random()->user_id,
					'author_type' => \App\User::class
				];
				$comment = factory(App\Comment::class, 1)->create($comment);
				$arrComments[] = $comment;
			}
		}

		// like
		$arrLike = [];

		// like: post
		foreach($arrPosts as $post) {
			$max = $users[$post->hub_id]->count();
			$count = $faker->numberBetween(0, $max);
			if($count == 0) {
				continue;
			}
			$subUsers = $users[$post->hub_id]->random($count);
			if(!($subUsers instanceof \Illuminate\Support\Collection)) {
				$subUsers = collect([$subUsers]);
			}
			foreach($subUsers as $i => $user) {
				$like = [
					'content_id' => $post->id,
					'content_type' => \App\Post::class,
					'liker_id' => $user->id,
					'liker_type' => \App\User::class
				];
				$arrLike[] = factory(App\Like::class, 1)->create($like);
			}
		}

		// like: comment
		foreach($arrComments as $comment) {
			$max = $users[$comment->hub_id]->count();
			$count = $faker->numberBetween(1, $max);
			if($count == 0) {
				continue;
			}
			$subUsers = $users[$comment->hub_id]->random($count);
			if(!($subUsers instanceof \Illuminate\Support\Collection)) {
				$subUsers = collect([$subUsers]);
			}
			foreach($subUsers as $i => $user) {
				$like = [
					'content_id' => $comment->id,
					'content_type' => \App\Comment::class,
					'liker_id' => $user->id,
					'liker_type' => \App\User::class
				];
				$arrLike[] = factory(App\Like::class, 1)->create($like);
			}
		}

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  post");
		$this->command->getOutput()->writeln("<info>Table:</info>  post_attachment");
		$this->command->getOutput()->writeln("<info>Table:</info>  comment");
		$this->command->getOutput()->writeln("<info>Table:</info>  like");
		$this->command->getOutput()->writeln("");
	}
}
