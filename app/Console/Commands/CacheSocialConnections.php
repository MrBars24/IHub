<?php

namespace App\Console\Commands;

// App
use App\ApiPaging;
use App\LinkedAccount;
use App\FacebookConnection;
use App\TwitterConnection;
use App\InstagramConnection;

// Laravel
use Illuminate\Console\Command;
use DB;

class CacheSocialConnections extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'social:cache-connections';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache social connections for various platforms';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('calling handleFacebookConnections');
		$this->handleFacebookConnections();
		$this->info('calling handleTwitterConnections');
		$this->handleTwitterConnections();
		$this->info('calling handleInstagramConnections');
		$this->handleInstagramConnections();
	}

	/**
	 * Handle Facebook connections
	 */
	private function handleFacebookConnections()
	{
		// get linked account api info
		// - facebook
		$accounts = LinkedAccount::apiInfo('facebook')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryFacebookConnections();

				// cache each page (update or insert)
				$this->cacheResults($account->native_id, with(new FacebookConnection), $data, $account->platform, $account->token);
			} catch(\Exception $e) {
				$this->info('Account expired for ' . $account->platform . ' (' . $account->native_id . ')?');
				$this->info('Message: ' . $e->getMessage());
				$account->expire($e->getMessage());
			}
		}
	}

	/**
	 * Handle Twitter connections
	 */
	private function handleTwitterConnections()
	{
		// get linked account api info
		// - twitter
		$accounts = LinkedAccount::apiInfo('twitter')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryTwitterConnections();

				// cache each page (update or insert)
				$this->cacheResults($account->native_id, with(new TwitterConnection), $data, $account->platform, $account->token);
			} catch(\Exception $e) {
				$this->info('Account expired for ' . $account->platform . ' (' . $account->native_id . ')?');
				$this->info('Message: ' . $e->getMessage());
				$account->expire($e->getMessage());
			}
		}
	}

	/**
	 * Handle Instagram connections
	 */
	private function handleInstagramConnections()
	{
		// get linked account api info
		// - instagram
		$accounts = LinkedAccount::apiInfo('instagram')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryInstagramConnections();

				// cache each page (update or insert)
				$this->cacheResults($account->native_id, with(new InstagramConnection), $data, $account->platform, $account->token);
			} catch(\Exception $e) {
				$this->info('Account expired for ' . $account->platform . ' (' . $account->native_id . ')?');
				$this->info('Message: ' . $e->getMessage());
				$account->expire($e->getMessage());
			}
		}
	}

	/// Section: Helpers

	/**
	 * Cache results from api calls
	 *
	 * @param string $native_id
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param array $data
	 * @param string $platform
	 * @param string $token
	 */
	private function cacheResults($native_id, \Illuminate\Database\Eloquent\Model $model, $data, $platform, $token)
	{
		// keep track of ids to exclude from disable query
		$exclude = [];

		// store data
		foreach($data->data as $item) {
			$store = $model->newQuery()->firstOrNew([
				'native_id'  => $native_id,
				'profile_id' => $item->profile_id,
			]);
			$store->fill([
				'native_id'      => $native_id,
				'profile_id'     => $item->profile_id,
				'display_name'   => $item->display_name,
				'screen_name'    => $item->screen_name,
				'type'           => $item->type,
				'avatar'         => $item->avatar,
				'access_token'   => $item->access_token,
				'end_point_type' => $item->end_point_type,
				//'follower_count' => $item->follower_count,
			   'is_active'      => true,
			]);
			$exists = $store->exists;
			$store->save();

			$exclude[] = $store->id;

			// cached row info
			$this->info('Cached ' . get_class($model) . ' "' . $item->display_name . '" (' . $item->profile_id . ') for account ' . $native_id . ' using ' . (!$exists ? 'insert' : 'update'));
		}

		// disable any previously cached results that were missed in the data above
		$count = $model->newQuery()
			->where('native_id', '=', $native_id)
			->whereNotIn('id', $exclude)
			->update([
				'is_active' => false
			]);

		// disable count info
		$this->info('Disabled ' . $count . ' ' . get_class($model) . ' records for account ' . $native_id . ' that were missed in the api query');

		// set next page for each end point type
		foreach($data->next_page as $type => $next_page) {
			$paging = ApiPaging::query()
				->where('token', '=', $token)
				->where('platform', '=', $platform)
				->where('end_point_type', '=', $type)
				->first();
			if(is_null($paging)) {
				$paging = new ApiPaging;
				$paging->fill([
					'token' => $token,
					'platform' => $platform,
					'end_point_type' => $type,
				]);
			}
			$paging->next_page = $next_page;
			$paging->save();

			// disable count info
			$this->info('Next page for ' . get_class($model) . ' for account ' . $native_id . ' and end point type "' . $type . '" set to "' . $next_page . '"');
		}
	}
}
