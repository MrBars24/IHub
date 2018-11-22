<?php

namespace App\Http\Controllers\Master;

// App
use App\FacebookConnection;
use App\TwitterConnection;
use App\InstagramConnection;
use App\Http\Controllers\Controller;
use App\Hub;
use App\Post;
use App\PostDispatchQueueItem;
use App\GigFeedPost;
use App\GigFeed;
use App\User;
use App\GigPost;
use App\Gig;
use App\Notification;
use App\LoginHistory;

// Laravel
use Illuminate\Http\Request;

class LogController extends Controller
{

	/**
	* GET /master/logs
	* ROUTE master::logs [web.php]
	*
	* The master logs index page contains action
	*
	* @return \Illuminate\Http\Response
	*/
	public function index(){
		return view('master.log.index');
	}

	/**
	* GET /master/logs/{logtype}
	* ROUTE master::logs [web.php]
	*
	* The logs page, this can display a number of logs
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  string                    $logType
	* @return \Illuminate\Http\Response
	*/
	public function getLogs(Request $request, $logType)
	{
		$logs = [];
		switch($logType) {
			case 'postdispatch':
				$logs = $this->getPostDispatchQueueLog();
				break;
			case 'migration': 
				$logs = $this->getMigrationLog();
				break;
			case 'gigfeedpost';
				$logs = $this->getGigFeedPostLog();
				break;
			case 'gigfeed';
				$logs = $this->getGigFeedLog();
				break;
			case 'facebookconnections';
				$logs = $this->getFacebookConnectionLog();
				break;
			case 'twitterconnections';
				$logs = $this->getTwitterConnectionLog();
				break;
			case 'instagramconnections';
				$logs = $this->getInstagramConnectionLog();
				break;
			case 'notification';
				$logs = $this->getNotificationsLog();
				break;
			case 'sessions';
				$logs = $this->getSessionsLog();
				break;
			case 'login-histories';
				$logs = $this->getLoginHistoriesLog();
				break;
		}
		
		$data = [
			'logs' => $logs,
			'active_sidebar' => 'logs'
		];
		
		// response
		return view('master.logs', $data);
	}
	
	/// Section: Helpers
	
	/**
	 * Get Migration Logs
	 *
	 * @return array
	 */
	private function getMigrationLog()
	{
		// set columns
		$columns = [
			'migration' => ['label' => 'Migration Name']
		];
		
		$items = \DB::table('migrations')->paginate(100);
		
		// response
		$logs = [
			'title' => 'Migration Logs',
			'columns' => $columns,
			'items' => $items
		];
		return $logs;
	}

	/**
	 * Get notification logs
	 * 
	 * @return array
	 */
	private function getNotificationsLog()
	{
		// get columns
		$columns = [
			'notification_label' => ['label' => 'Notification Type'],
			'hub_name'          => ['label' => 'Hub'],
			'receiver_name'     => ['label' => 'Receiver'],
			'sender_name'       => ['label' => 'Sender'],
			'link'              => ['label' => 'Link'],
			'summary'           => ['label' => 'Summary'],
			'message'           => ['label' => 'message'],
			'profile'           => ['label' => 'Profile' ]
		];

		$items = Notification::with([
				'receiver' => function($query) {
					$query->select('receiver.id', 'receiver.name as receiver_name');
				},
				'sender' => function($query) {
					$query->select('receiver.id', 'sender.name as sender_name');
				},
				'hub' => function($query) {
					$query->select('hub.id', 'hub.name as hub_name');
				},
				'type' => function($query) {
					$query->select('notification_type.id', 'notification_type.label as notification_label');
				}
			])
			->orderBy('created_at', 'DESC')
			->paginate(100);

		// response
		return [
			'title'   => 'Notification Logs',
			'columns' => $columns,
			'items'   => $items
		];
	}
	
	/**
	 * Get Gig Feed Log
	 *
	 * @return array
	 */
	private function getGigFeedLog()
	{
		// set columns
		$columns = [
			'hub_name'        => ['label' => 'Hub'],
			'type'            => ['label' => 'Type'],
			'source_url'      => ['label' => 'Source Url'],
			'evaluated_url'   => ['label' => 'Evaluated Url'],
			'last_result'     => ['label' => 'Last Result'],
			'last_error'      => ['label' => 'Last Error']
		];

		$items = GigFeed::query()
			->join('hub', 'hub.id', '=', 'gig_feed.hub_id')
			->select(['gig_feed.*', 'hub.name as hub_name'])
			->paginate(100);

		// response
		return [
			'title' => 'Gig Feed Logs',
			'columns' => $columns,
			'items' => $items
		];
	}
	
	/**
	 * Get Gig Feed Post Log
	 *
	 * @return array
	 */
	private function getGigFeedPostLog()
	{
		// set columns
		$columns = [
			'type'          => ['label' => 'Type'],
			'title'         => ['label' => 'Title'],
			'description'   => ['label' => 'Description'],
			'url_profile'   => ['label' => 'Url']
		];

		$items = GigFeedPost::query()
			->paginate(100);

		// response
		return [
			'title' => 'Gig Feed Post Logs',
			'columns' => $columns,
			'items' => $items
		];
	}
	
	/**
	 * Get Post Dispatch Queue Log
	 *
	 * @return array
	 */
	private function getPostDispatchQueueLog()
	{
		// set columns
		$columns = [
			'hub.name'         => ['label' => 'Hub'],
			'user.name'        => ['label' => 'User'],
			'social.name'      => ['label' => 'Social Handle'],
			'platform'         => ['label' => 'Platform'],
			'attachment.type'  => ['label' => 'Attachment Type'],
			'message'          => ['label' => 'Message'],
			'created_at'       => ['label' => 'Created At'],
			'started_at'       => ['label' => 'Started At'],
			'finished_at'      => ['label' => 'Finished At'],
			'result'           => ['label' => 'Result'],
			'error'            => ['label' => 'Error'],
			'attempts'         => ['label' => 'Attempts'],
			'certainty'        => ['label' => 'certainty']
		];
		
		$items = PostDispatchQueueItem::with([
			'hub',
			'social',
			'user',
			'attachment'
		])
			->orderBy('created_at', 'DESC')
			->paginate(100);

		// response
		$logs = [
			'title' => 'Post Dispatch Queue Logs',
			'columns' => $columns,
			'items' => $items,
		];
		return $logs;
	}

	/**
	 * Get Facebook Connection Log
	 *
	 * @return array
	 */
	private function getFacebookConnectionLog()
	{
		// set columns
		$columns = [
			'native_id'        => ['label' => 'Native ID'],
			'screen_name'      => ['label' => 'Screen Name'],
			'display_name'     => ['label' => 'Display Name'],
			'type'             => ['label' => 'Type'],
			'avatar'           => ['label' => 'Avatar'],
			'access_token'     => ['label' => 'Access Token'],
			'end_point_type'   => ['label' => 'End Point Type'],
			'created_at'       => ['label' => 'Created At'],
			'updated_at'       => ['label' => 'Updated At'],
			'is_active'        => ['label' => 'Is Active']
		];

		$items = FacebookConnection::query()
			->orderBy('updated_at', 'DESC')
			->paginate(100);

		// response
		$logs = [
			'title' => 'Facebook Connection Logs',
			'columns' => $columns,
			'items' => $items,
		];
		return $logs;
	}

	/**
	 * Get Twitter Connection Log
	 *
	 * @return array
	 */
	private function getTwitterConnectionLog()
	{
		// set columns
		$columns = [
			'native_id'        => ['label' => 'Native ID'],
			'screen_name'      => ['label' => 'Screen Name'],
			'display_name'     => ['label' => 'Display Name'],
			'type'             => ['label' => 'Type'],
			'avatar'           => ['label' => 'Avatar'],
			'access_token'     => ['label' => 'Access Token'],
			'end_point_type'   => ['label' => 'End Point Type'],
			'created_at'       => ['label' => 'Created At'],
			'updated_at'       => ['label' => 'Updated At'],
			'is_active'        => ['label' => 'Is Active']
		];

		$items = TwitterConnection::query()
			->orderBy('updated_at', 'DESC')
			->paginate(100);

		// response
		$logs = [
			'title' => 'Twitter Connection Logs',
			'columns' => $columns,
			'items' => $items,
		];
		return $logs;
	}

	/**
	 * Get Instagram Conenction Log
	 *
	 * @return array
	 */
	private function getInstagramConnectionLog()
	{
		// set columns
		$columns = [
			'native_id'        => ['label' => 'Native ID'],
			'screen_name'      => ['label' => 'Screen Name'],
			'display_name'     => ['label' => 'Display Name'],
			'type'             => ['label' => 'Type'],
			'avatar'           => ['label' => 'Avatar'],
			'access_token'     => ['label' => 'Access Token'],
			'end_point_type'   => ['label' => 'End Point Type'],
			'created_at'       => ['label' => 'Created At'],
			'updated_at'       => ['label' => 'Updated At'],
			'is_active'        => ['label' => 'Is Active']
		];

		$items = InstagramConnection::query()
			->orderBy('updated_at', 'DESC')
			->paginate(100);

		// response
		$logs = [
			'title' => 'Instagram Connection Logs',
			'columns' => $columns,
			'items' => $items,
		];
		return $logs;
	}

	/**
	 * Get Session Log
	 *
	 * @return array
	 */
	private function getSessionsLog()
	{
		// set columns
		$columns = [
			'id'            => ['label' => 'ID'],
			'user_id'       => ['label' => 'User ID'],
			'ip_address'    => ['label' => 'IP Address'],
			'user_agent'    => ['label' => 'User Agent'],
			'payload'       => ['label' => 'Payload'],
			'last_activity' => ['label' => 'Last Activity'],
		];

		$items = \DB::table('sessions')
			->paginate(100);

		// response
		$logs = [
			'title' => 'Session Logs',
			'columns' => $columns,
			'items' => $items
		];

		return $logs;
	}

	private function getLoginHistoriesLog()
	{
		// set columns
		$columns = [
			'user_id'      => ['label' => 'User ID'],
			'user_name'    => ['label' => 'User Name'],
			'ip_address'   => ['label' => 'IP Address'],
			'user_agent'   => ['label' => 'User Agent'],
			'device_token' => ['label' => 'Device Token'],
			'device_os'    => ['label' => 'Device OS'],
		];

		$items = LoginHistory::query()
			->join('user', 'user.id', '=', 'login_history.user_id')
			->select(['login_history.*', 'user.name as user_name', 'user.id as user_id'])
			->latest()
			->paginate(100);

		// response
		$logs = [
			'title' => 'Login History Logs',
			'columns' => $columns,
			'items' => $items,
		];
		return $logs;
	}
}