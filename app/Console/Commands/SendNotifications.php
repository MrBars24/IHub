<?php

namespace App\Console\Commands;

// App
use App\PushNotificationQueueItem;
use App\Modules\Notifications\PushBots;

use Illuminate\Console\Command;

class SendNotifications extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'notifications:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send out queued push notifications';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// get ALL push notification queue items that have not been successfully processed. Take care to only retrieve ones with current sessions
		// @todo: get the session info (device_token, device_os) from login_history of user.
		$queue = PushNotificationQueueItem::query()
			->with([
				'user' => function ($query) {
					$query->where('receive_push_notifications', '=', true)
						->where('is_active', '=', true);
				},
				'user.login_histories' => function($query) {
					$query->whereNotNull('device_token')
						->whereNotNull('device_os');
				},
				'notification'
			])
			->where('result', '=', 'pending')
			->get();

		// process all items
		foreach($queue as $i => $item) {
			$item->started_at = carbon();
			$item->result = 'started';
			$item->save();

			// check point: make sure post, user, and social account can be identified
			if(is_null($item->user)) {
				$item->finished_at = carbon();
				$item->result = 'error';
				$item->error = 'Suitable user not found; aborting';
				$item->save();
				continue;
			}

			// this function will catch any errors and return 'error'
			$result = null;
			$resultText = null;
			$resultError = null;
			try{
				$result = $this->send($item);
				$resultText = $result;
				$resultError = null;
				if($result !== 'success') {
					$resultText = 'error';
					$resultError = $result;
				}
			}
			catch(\Exception $e) {
				$resultText = 'error';
				$resultError = [
					'code' => $e->getCode(),
					'message' => $e->getMessage(),
				];
				$resultError = json_encode($resultError);
			}

			// round off item
			$item->finished_at = carbon();
			$item->result = $resultText;
			$item->error = $resultError;
			$item->save();
		}
	}

	/// Section: Helpers


	private function send($item)
	{
		// initialize pushbot object
		$pushBots = new PushBots;
		$appID     = config('services.pushbots.app_id');
		$appSecret = config('services.pushbots.app_secret');
		$pushBots->App($appID, $appSecret);

		// send to each device
		$return = 'No messages sent';
		$deviceIds = [];

		$loginHistories = $item->user->login_histories;
		$notification = $item->notification;

		foreach($loginHistories as $i => $session) {
			// weed out duplicate device tokens
			if(isset($deviceIds[$session->device_token])) {
				continue;
			}
			$deviceIds[$session->device_token] = true;

			// get platform code for pushbots
			$platform = '';
			switch(strtolower($session->device_os)) {
				case 'ios':
					$platform = '0';
					break;
				case 'android':
					$platform = '1';
					break;
				default:
					if($return !== 'success') {
						$return = 'Device "' . $session->device_os . '" not supported';
					}
					break;
			}

			// pushbot notification
			$pushBots->TokenOne($session->device_token);
			$pushBots->AlertOne($notification->message);
			$pushBots->PlatformOne($platform);
			$pushBots->BadgeOne("+1");
			//$pb->BadgeOne($badgeCount);
			$pushBots->PayloadOne(array(
				'url' => $notification->url
			));
			$result = $pushBots->PushOne();

			// // get result
			// if($result['status'] === 'OK') {
			// 	$return = 'success';
			// 	// push status
			// 	$item->result = 'success';
			// 	$item->save();
			// } elseif($return !== 'success') {
			// 	$return = $result['code'] . ': ' . $result['success'];
			// 	// push status
			// 	$item->result = 'error';
			// 	$item->error = $return;
			// 	$item->save();
			// }

			// get result
			if($result['status'] === 'OK') {
				$return = 'success';
			}
			elseif($return !== 'success') {
				$return = $result['code'] . ': ' . $result['data'];
			}
		}
		return $return;
	}
}
