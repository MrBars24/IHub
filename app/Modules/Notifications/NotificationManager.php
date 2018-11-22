<?php

namespace App\Modules\Notifications;

// App
use App\Notification;
use App\NotificationType;
use App\PushNotificationQueueItem;
use App\User;
use App\Events\Notification\SendPush;

// Laravel
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class NotificationManager
{
	/**
	 * @var \Illuminate\Support\Collection
	 */
	protected $types = null;

	/**
	 * NotificationManager constructor
	 */
	public function __construct()
	{
		$this->populateNotificationTypes();
	}

	/**
	 * Notify recipients of a certain event in the application
	 *
	 * @param string $event
	 * @param array|\Illuminate\Support\Collection $recipients
	 * @param mixed $sender
	 * @param string $link
	 * @param string $summary
	 * @param string $message
	 * @param array $params
	 */
	public function notify($event, $recipients, $sender, $link, $summary, $message, $params = [])
	{
		// ensure collection
		if(!($recipients instanceof \Illuminate\Support\Collection)) {
			$recipients = collect([$recipients]);
		}

		// get notification type
		$type = $this->types[$event];
		if(is_null($type)) {
			throw new \Exception('Notification type not found');
		}

		// create individual notifications in here for each recipient
		$notifications = collect([]);
		foreach($recipients as $i => $recipient) {
			$notification = new Notification();
			$notification->type()->associate($type);
			$notification->receiver()->associate($recipient);
			$notification->sender()->associate($sender);
			$notification->profile = $type->profile;
			$notification->link = $link;
			$notification->summary = $summary;
			$notification->message = $message;

			// assign individual params to object, this is more flexible than just using the fill method as it caters for relations as well
			foreach($params as $key => $value) {
				// basic attribute: assign directly
				if(is_scalar($notification->getAttribute($key))) {
					$notification->setAttribute($key, $value);
				}
				// relation: assign or associate
				elseif(method_exists($notification, $key) && is_callable([$notification, $key])) {
					$relation = $notification->$key();
					if($relation instanceof Model) {
						$notification->setRelation($key, $value);
					} else {
						$relation->associate($value);
					}
				}
			}
			$notification->save(); // @note SP added a save call here, I think not having this is breaking the gig notify script

			// collect for later use
			$notifications[] = $notification;
		}

		// get settings
		$settings = $this->getNotificationSettings($notifications);

		// send out each notification to the receivers based on account settings configurations
		// - web
		// - email
		// - push
		foreach($notifications as $notification) {

			// get relevant setting values
			$receivers = $settings
				->filter(function($user) use ($notification) {

					// get owners of recipient
					$owners = $notification->receiver->getOwners();

					return isset($owners[$user->id])                // receiver
						&& $user->type_id == $notification->type_id  // type
						&& $user->hub_id  == $notification->hub_id;  // hub
				});

			// cycle receivers
			// typically, there should only be 1 item in the collection unless there are multiple hub managers in a hub who need to receive the notification
			foreach($receivers as $setting) {

				// web
				if($setting->send_web) {
					$this->sendToWeb($notification, $setting);
				}

				// email
				if($setting->send_email) {
					$this->sendToEmail($notification, $setting);
				}

				// push
				if($setting->send_push) {
					$this->sendToPush($notification, $setting);
				}
			}
		}
	}

	/**
	 * Get notification settings for all recipients in the passed notifications
	 *
	 * @param \Illuminate\Support\Collection $notifications
	 * @return \Illuminate\Support\Collection
	 */
	protected function getNotificationSettings(\Illuminate\Support\Collection $notifications)
	{
		// get recipients - a list of user ids
		$ids = [];
		foreach($notifications as $notification) {
			$ids = array_merge($ids, $notification->receiver->getOwners()->pluck('id')->toArray());
		}
		$ids = array_unique($ids);

		// get notification types
		$types = $notifications->unique('type_id')->pluck('type_id')->toArray();

		// get hubs
		$hubs = $notifications->unique('hub_id')->pluck('hub_id')->toArray();

		// get the final recipients of all notifications
		$settings = User::select([
			'notification_setting.*',
			'user.id AS id',
			'user.name',
			'user.email',
			'membership.id AS membership_id',
			'membership.hub_id'
		])
			->leftJoin('membership', 'user.id', '=', 'membership.user_id')
			->leftJoin('notification_setting', 'membership.id', '=', 'notification_setting.membership_id')
			->whereIn('user.id', $ids)
			->whereIn('notification_setting.type_id', $types)
			->whereIn('membership.hub_id', $hubs)
			->get();
		return $settings;
	}

	/**
	 * Send a notification within the web interface
	 *
	 * @param \App\Notification $notification
	 * @param \App\User $settings
	 */
	protected function sendToWeb(Notification $notification, User $settings)
	{
		$notification->emitted_to_web = true;
		$notification->save();
	}

	/**
	 * Send an email notification
	 *
	 * @param \App\Notification $notification
	 * @param \App\User $settings
	 */
	protected function sendToEmail(Notification $notification, User $settings)
	{
		// email
		$data = array(
			'notification' => $notification
		);
		// note: using objects here may have unexpected serialisation behaviour
		$notification_id = $notification->id;

		// site info
		$site_name = config('notifications.site.name');

		// sender info
		$sender_name = $notification->sender->name;
		$sender_email = $notification->sender->email;

		// config
		$profile = config('notifications.profiles.' . $notification->type->profile);

		// from:
		$from_name = $profile['from_name'];
		$from_name = str_replace('{sender.name}', $sender_name, $from_name);
		$from_name = str_replace('{site.name}', $site_name, $from_name);
		$from_email = $sender_email;

		// to:
		$to_name = $settings->name;
		$to_email = $settings->email;

		// subject:
		$subject = $profile['subject'];
		$subject = str_replace('{sender.name}', $sender_name, $subject);
		$subject = str_replace('{site.name}', $site_name, $subject);

		// queue the email
		Mail::queue($profile['template'], $data, function($message) use ($notification_id, $from_name, $from_email, $to_email, $to_name, $subject) {
			// track email sent
			$notification = Notification::find($notification_id);
			$notification->emitted_to_email = true;
			$notification->save();

			// compile message
			$message
				->from($from_email, $from_name)
				->to($to_email, $to_name)
				->subject($subject);
		});
	}

	/**
	 * Send a push notification to relevant devices logged into the app
	 *
	 * @param \App\Notification $notification
	 * @param \App\User $settings
	 */
	protected function sendToPush(Notification $notification, User $settings)
	{
		// queue notification
		$item = new PushNotificationQueueItem;
		$item->notification()->associate($notification);
		$item->user()->associate($settings);
		$item->message = $notification->message;
		$item->url = $notification->link;
		$item->result = 'pending';
		$item->save();

		// broadcast a realtime event.
		// we can move this insde a model event observer. 
		broadcast(new SendPush($item));
	}

	/**
	 * Populate the notification manager with the app's list of notification types
	 */
	public function populateNotificationTypes()
	{
		if(is_null($this->types)) {
			$types = NotificationType::query()
				->get()
				->keyBy('key');
			$this->types = $types;
		}
	}
}
