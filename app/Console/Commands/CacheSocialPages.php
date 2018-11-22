<?php

namespace App\Console\Commands;

// App
use App\LinkedAccount;
use App\FacebookPageAccess;
use App\LinkedinCompanyAccess;
use App\PinterestBoardAccess;

// Laravel
use Illuminate\Console\Command;
use DB;

class CacheSocialPages extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'social:cache-pages';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cache social pages for various platforms';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->handleFacebookPages();
		$this->handlePinterestBoards();
		$this->handleLinkedinCompanies();
	}

	/// Section: Social Platforms

	private function handleFacebookPages()
	{
		// get linked account api info
		// - facebook
		$accounts = LinkedAccount::apiInfo('facebook')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryFacebookPages();

				// cache each page (update or insert)
				$this->cacheResults($account->id, $account->native_id, with(new FacebookPageAccess), $data);
			} catch(\Exception $e) {
				$this->info('Account expired for ' . $account->platform . ' (' . $account->native_id . ')?');
				$this->info('Message: ' . $e->getMessage());
				$account->expire($e->getMessage());
			}
		}
	}

	private function handlePinterestBoards()
	{
		// get linked account api info
		// - pinterest
		$accounts = LinkedAccount::apiInfo('pinterest')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryPinterestBoards();

				// cache each page (update or insert)
				$this->cacheResults($account->id, $account->native_id, with(new PinterestBoardAccess), $data);
			} catch(\Exception $e) {
				$this->info('Account expired for ' . $account->platform . ' (' . $account->native_id . ')?');
				$this->info('Message: ' . $e->getMessage());
				$account->expire($e->getMessage());
			}
		}
	}

	private function handleLinkedinCompanies()
	{
		// get linked account api info
		// - facebook
		$accounts = LinkedAccount::apiInfo('linkedin')
			->get()
			->pluck('token_info');

		// for each account, retrieve facebook pages that the account administrates
		foreach($accounts as $account) {
			try {
				$data = $account->queryLinkedinCompanies();

				// cache each page (update or insert)
				$this->cacheResults($account->id, $account->native_id, with(new LinkedinCompanyAccess), $data);
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
	 * @param integer $linked_id
	 * @param string $native_id
	 * @param \Illuminate\Database\Eloquent\Model $model
	 * @param array $data
	 */
	private function cacheResults($linked_id, $native_id, \Illuminate\Database\Eloquent\Model $model, $data)
	{
		// keep track of ids to exclude from disable query
		$exclude = [];

		// store data
		foreach($data as $item) {
			$store = $model->newQuery()->firstOrNew([
				'native_id'  => $native_id,
				'profile_id' => $item->profile_id,
			]);
			$store->fill([
				'linked_id'      => $linked_id,
				'native_id'      => $native_id,
				'profile_id'     => $item->profile_id,
				'name'           => $item->name,
				'type'           => $item->type,
				'avatar'         => $item->avatar,
				'access_token'   => $item->access_token,
				'follower_count' => $item->follower_count,
			   'is_active'      => true,
			]);
			$exists = $store->exists;
			$store->save();

			$exclude[] = $store->id;

			// cached row info
			$this->info('Cached ' . get_class($model) . ' "' . $item->name . '" (' . $item->profile_id . ') for account ' . $native_id . ' using ' . (!$exists ? 'insert' : 'update'));
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
	}
}
