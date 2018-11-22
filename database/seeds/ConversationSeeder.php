<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ConversationSeeder extends Seeder
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
		$users = App\User::query()
			->join('membership', 'membership.user_id', '=', 'user.id')
			->where('is_master', '=', false)
			->where('membership.role', '=', 'influencer')
			->select([
				'user.id',
				'membership.hub_id'
			])
			->get()
			->groupBy('hub_id');

		// conversation
		$arrConversations = [];
		foreach($hubs as $i => $hub) {
			$count = $faker->numberBetween(3, 11);
			for($j = 0; $j < $count; $j++) {
				$useHub = $faker->boolean(30);
				// use 1 influencer and 1 hub
				if($useHub) {
					$pair = collect([
						$users[$hub->id]->random(1),
						$hub
					]);
				}
				// use 2 influencers
				else {
					$pair = $users[$hub->id]->random(2);
				}

				// prepare data
				$sender_id = $pair->first()->id;
				$sender_type = get_class($pair->first());
				$receiver_id = $pair->last()->id;
				$receiver_type = get_class($pair->last());
				$sender = $sender_id . ':' . $sender_type;
				$receiver = $receiver_id . ':' . $receiver_type;

				// detect if key exists, we check from both directions
				$key = $sender . ',' . $receiver;
				$key2 = $receiver . ',' . $sender;
				if(isset($arrConversations[$key]) || isset($arrConversations[$key2])) {
					continue;
				}
				$conversation = [
					'hub_id' => $hubs->random()->id,
					'sender_id' => $sender_id,
					'sender_type' => $sender_type,
					'receiver_id' => $receiver_id,
					'receiver_type' => $receiver_type,
				];
				$arrConversations[$key] = factory(App\Conversation::class, 1)->create($conversation);
			}
		}

		// message
		foreach($arrConversations as $key => $conversation) {
			$pair = explode(',', $key);
			$count = $faker->numberBetween(3, 13);
			for($i = 0; $i < $count; $i++) {
				$senderIndex = ($i == 0 ? 0 : $faker->numberBetween(0, 1));
				$sender = explode(':', $pair[$senderIndex]);
				$id = $sender[0];
				$type = $sender[1];
				$message = [
					'conversation_id' => $conversation->id,
					'sender_id' => $id,
					'sender_type' => $type,
				];
				$message = factory(App\Message::class, 1)->create($message);
			}
			// save last message
			$conversation->last_message_id = $message->id;
			$conversation->save();
		}

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  conversation");
		$this->command->getOutput()->writeln("<info>Table:</info>  message");
		$this->command->getOutput()->writeln("");
	}
}
