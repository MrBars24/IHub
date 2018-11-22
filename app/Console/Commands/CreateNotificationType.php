<?php

namespace App\Console\Commands;

// App
use App\Membership;
use App\NotificationType;
use DB;

// Laravel
use Illuminate\Console\Command;

class CreateNotificationType extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'notification-types:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new notification type and roll it out to all members';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// input
		$label       = $this->ask('Notification Type: Label (eg: My Label)');
		$key         = $this->ask('Notification Type: Key (eg: context.something)');
		$profile     = $this->choice('Notification Type: Profile', ['notification', 'direct_message']);
		$enabled_for = $this->choice('Notification Type: Enabled for', ['all', 'hubmanager', 'influencer']);
		$send_web    = $this->choice('Notification Type: Send web', ['yes', 'no']);
		$send_email  = $this->choice('Notification Type: Send email', ['yes', 'no']);
		$send_push   = $this->choice('Notification Type: Send push', ['yes', 'no']);

		// summary
		$this->info('Label: ' . $label);
		$this->info('Key: ' . $key);
		$this->info('Profile: ' . $profile);
		$this->info('Enabled for: ' . $enabled_for);
		$this->info('Send web: ' . $send_web);
		$this->info('Send email: ' . $send_email);
		$this->info('Send push: ' . $send_push);

		// confirm?
		if(!$this->confirm('Confirm? [y|N]')) {
			die;
		}

		$send_web   = $send_web == 'yes';
		$send_email = $send_email == 'yes';
		$send_push  = $send_push == 'yes';

		// commence running command

		// create type
		$type = new NotificationType;
		$type->label = $label;
		$type->key = $key;
		$type->profile = $profile;
		$type->enabled_for = $enabled_for;
		$type->send_web = $send_web;
		$type->send_email = $send_email;
		$type->send_push = $send_push;
		$type->save();

		// get all member settings and inserting missing notification type settings
		$members = Membership::query()
			->select('id')
			->whereNotIn('id', function($query) use ($type) {
				$query->select('membership_id')
					->from('notification_setting')
					->where('type_id', '=', $type->id);
			})
			->get();

		// inserts
		$inserts = array();
		foreach($members as $i => $member) {
			// check enabled for: hub manager
			if($type->enabled_for == 'hubmanager' && $member->role <> 'hubmanager') {
				continue;
			}
			// check enabled for: influencer
			elseif($type->enabled_for == 'influencer' && $member->role <> 'influencer') {
				continue;
			}

			// row
			$inserts[] = array(
				'send_web' => $send_web,
				'send_email' => $send_email,
				'send_push' => $send_push,
				'type_id' => $type->id,
				'membership_id' => $member->id
			);
		}
		DB::table('notification_setting')->insert($inserts);
	}
}
