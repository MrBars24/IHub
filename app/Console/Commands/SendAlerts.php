<?php

namespace App\Console\Commands;

// App
use App\Alert;
use App\AlertCycle;
use App\Gig;
use App\Hub;
use App\AlertSent;

// Laravel
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendAlerts extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'alerts:send';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Send scheduled alerts';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$now = carbon();

		// get ALL active gigs created in the last 2 weeks
		$gigs = Gig::with([
			'categories',
			'platforms',
		])
			->where('deadline_at', '>', $now)
			->where('created_at', '>', $now->copy()->addDays(-14))
			->where('is_active', '=', true)
			->where('is_live', '=', true)
			->get();

		// get sent alerts record
		// this is mainly to make sure that we don't send the same gig to a given user more than once
		$sentAlerts = AlertSent::all();

		// hubs, memberships, users
		$hubs = Hub::with([
			'members' => function($query) {
				$query->select('membership.*')
					->leftJoin('user', 'membership.user_id', '=', 'user.id')
					->where('user.is_active', '=', true)
					->where('membership.is_active', '=', true)
					->where('membership.send_alerts', '=', true);

				// development environment
				if(app()->environment() == 'local') {
					$query->whereIn('user.email', array(
						'satoshi.payne@gmail.com',
						'satoshi.payne@hotmail.com',
					));
				}
				// development environment
				elseif(app()->environment() == 'staging') {
					$query->whereIn('user.email', array(
						'satoshi.payne@gmail.com',
						'satoshi.payne@hotmail.com',
						'satoshi@bodecontagion.com',
						'rderrington@themediabag.com',
						'rderrington@sourcebottle.com.au',
						'raderrington@iprimus.com.au',
					));
				}
			},
			'members.latestAlert' => function($query) {
				$query->whereNotNull('sent_at')
					->orderBy('sent_at', 'DESC');
			},
			'members.user',
			'members.categories',
			'members.platforms',
		])
			->where('is_active', '=', true)
			->get();

		// alert frequencies to number of days map
		$alertFrequencies = [
			'fortnight' => 14,
			'week'      => 7,
			'halfweek'  => 3,
			'day'       => 1,
		];

		// loop through each hub, then membership
		foreach($hubs as $i => $hub) {
			$this->info('looping hub: ' . $hub->id);

			// create alert cycle
			$date = date('Y-m-d-h-i');
			$cycle = new AlertCycle;
			$cycle->hub_id = $hub->id;
			$cycle->name = "hub#{$hub->id}/$date";
			$cycle->save();

			// filter by hub
			foreach($hub->members as $j => $membership) {
				$this->info('looping hub: ' . $hub->id . ', membership: ' . $membership->hub_id . '/' . $membership->id . ' (' . $membership->user->email . ')');

				// get number of days from frequency
				$days = $alertFrequencies[$membership->alert_frequency];

				// check point: ensure that the last alert date is further back than the number of days represented in alert frequency
				if(!is_null($membership->latestAlert)) {
					$dateClearance = $membership->latestAlert->sent_at->addDays($days);
					if($now < $dateClearance) {
						continue;
					}
				}

				// filter by:
				// - hub
				// - categories
				// - platforms
				// - sent alerts
				$subGigs = $gigs->filter(function($item) use ($membership, $sentAlerts) {
					$this->info('gig: ' . $item->id . ': membership: ' . $membership->id . ': hub: ' . $membership->hub_id);

					// hub
					if($item->hub_id != $membership->hub_id) {
						$this->info('gig not in hub');
						return false;
					}
					// categories
					$c1 = $membership->categories->pluck('id')->toArray();
					$c2 = $item->categories->pluck('id')->toArray();
					if(!empty($c1) && !empty($c2) && empty(array_intersect($c1, $c2))) {
						$this->info('not category selected');
						return false;
					}
					// platforms
					$p1 = $membership->platforms->pluck('id')->toArray();
					$p2 = $item->platforms->pluck('id')->toArray();
					if(!empty($p1) && !empty($p2) && empty(array_intersect($p1, $p2))) {
						$this->info('not platform selected');
						return false;
					}
					// sent alerts
					$userId = $membership->user_id;
					$sub = collect($sentAlerts)->filter(function($item) use ($userId) {
						return $item->user_id == $userId;
					})->pluck('gig_id')->toArray();
					if(!empty($sub) && in_array($item->id, $sub)) {
						$this->info('already sent gig out');
						return false;
					}

					$this->info('gig sent');
					return true;
				});

				// check point: make sure we're sending out an email with at least 1 gig
				if($subGigs->count() == 0) {
					continue;
				}

				// create alert instance
				$user = $membership->user;

				$alert = new Alert;
				$alert->hub_id = $hub->id;
				$alert->cycle_id = $cycle->id;
				$alert->cycle_name = $cycle->name;
				$alert->membership_id = $membership->id;
				$alert->email = $user->email;
				$alert->gig_count = $subGigs->count();
				$alert->save();
				$alert->gigs()->attach($subGigs->pluck('id')->toArray());

				// increment alerts for this cycle
				$cycle->alert_count++;

				// response
				$data = [
					'gigs' => $subGigs,
					'hub' => $hub,
					'user' => $user,
					'alert' => $alert,
				];
				// note: using objects here may have unexpected serialisation behaviour
				$alert_id = $alert->id;
				$user_name = $user->name;
				$user_email = $user->email;
				$hub_name = $hub->name;
				Mail::send('email.alert', $data, function($message) use ($user_name, $user_email, $hub_name, $alert_id) {
					// set sent
					$alert = Alert::find($alert_id);
					$alert->sent_at = carbon();
					$alert->save();

					// compile message
					$message
						->from('noreply@influencerhub.com', 'Influencer HUB')
						->to($user_email, $user_name)
						->subject($hub_name . ' HUB Gig Update');
				});
			}

			// finished hub cycle
			$cycle->finished_at = carbon();
			$cycle->save();
		}
	}
}
