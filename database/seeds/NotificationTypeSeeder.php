<?php

// Laravel
use Illuminate\Database\Seeder;

class NotificationTypeSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		// notification_type
		$notificationTypes = [
			'event.gig.published'       => ['name' => 'Gig Published',       'profile' => 'notification',   'enabled' => 'all'],
			'event.gig.expiring'        => ['name' => 'Gig Expiring',        'profile' => 'notification',   'enabled' => 'all'],
			'event.gig.expired'         => ['name' => 'Gig Expired',         'profile' => 'notification',   'enabled' => 'hubmanager'],
			'event.post.published'      => ['name' => 'Post Published',      'profile' => 'notification',   'enabled' => 'all'],
			'event.post.shared'         => ['name' => 'Post Shared',         'profile' => 'notification',   'enabled' => 'all'],
			'event.post.liked'          => ['name' => 'Post Liked',          'profile' => 'notification',   'enabled' => 'all'],
			'event.post.approved'       => ['name' => 'Post Approved',       'profile' => 'notification',   'enabled' => 'all'],
			'event.comment.published'   => ['name' => 'Comment Published',   'profile' => 'notification',   'enabled' => 'all'],
			'event.membership.accepted' => ['name' => 'Membership Accepted', 'profile' => 'notification',   'enabled' => 'all'],
			'event.membership.removed'  => ['name' => 'Membership Removed',  'profile' => 'notification',   'enabled' => 'all'],
			'event.message.created'     => ['name' => 'Private Messages',    'profile' => 'direct_message', 'enabled' => 'all'],
			'event.instagram.ready'     => ['name' => 'Instagram Ready',     'profile' => 'notification',   'enabled' => 'all'],
			'event.post.reported'       => ['name' => 'Post Reported',       'profile' => 'notification',   'enabled' => 'hubmanager']
		];
		foreach($notificationTypes as $key => $item) {
			$data = array(
				'label' => $item['name'],
				'key' => $key,
				'profile' => $item['profile'],
				'enabled_for' => $item['enabled'],
				'is_enabled' => true,
				'send_web' => true,
				'send_email' => true,
				'send_push' => true,
			);
			factory(App\NotificationType::class, 1)->create($data);
		}

		$this->command->getOutput()->writeln("");
		$this->command->getOutput()->writeln("<info>Table:</info>  notification_type");
		$this->command->getOutput()->writeln("");
	}
}
