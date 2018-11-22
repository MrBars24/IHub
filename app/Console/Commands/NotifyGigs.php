<?php

namespace App\Console\Commands;

// App
use App\Modules\Notifications\NotificationManager;
use App\Gig;
//use App\MembershipGroup;
use App\NotificationType;

// Laravel
use Illuminate\Console\Command;

class NotifyGigs extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'gigs:notify';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Notify uses of various gig related events';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('handleGigPublished');
		$this->handleGigPublished();
		$this->info('handleGigExpiring');
		$this->handleGigExpiring();
		$this->info('handleGigExpired');
		$this->handleGigExpired();
	}

	/**
	 * Handle gig.published event
	 */
	private function handleGigPublished()
	{
		$now = carbon();

		// get gigs
		// - active gigs
		// - in active hubs
		// - that have not been flagged as commenced
		// - the commenced date is in the past
		$gigs = Gig::with([
			'hub.members' => function($query) {
				$query->where('is_active', '=', true);
			},
			'hub.members.user' => function($query) {
				$query->where('is_active', '=', true);
			}
		])
			->join('hub', 'hub.id', '=', 'gig.hub_id')
			->where('hub.is_active', '=', true)
			->where('gig.is_active', '=', true)
			->where('gig.has_commenced_notified', '=', false)
			->where('gig.commence_at', '<=', $now)
			->select([
				'gig.*',
			])
			->get()
			->groupBy('hub_id');

		// send notifications
		foreach($gigs as $hubGroup) {
			$ids = [];
			$summary = [];
			$hub = $hubGroup->first()->hub;

			$this->info('Hub: ' . $hub->name . ' (' . $hub->id . ')');

			foreach($hubGroup as $gig) {
				$this->info('  Gig: ' . $gig->title . ' (' . $gig->id . ')');

				// get params
				$recipients = $gig->hub->members->pluck('user');

				// fire event: event.gig.published
				event('event.gig.published', ['event' => 'event.gig.published', 'gig' => $gig, 'hub' => $hub, 'recipients' => $recipients]);
				$ids[] = $gig->id;
				$summary[] = 'id:' . $gig->id . ' (' . $recipients->count() . ' users)';
			}

			// flag as notified
			Gig::whereIn('id', $ids)->update(['has_commenced_notified' => true, 'is_live' => true]);

			// info
			$this->info('Gigs commenced: ' . implode('; ', $summary));
		}
	}

	/**
	 * Handle gig.expiring event
	 */
	private function handleGigExpiring()
	{
		$cutoff = carbon()->addHours(8);

		// get gigs
		// - active gigs
		// - in active hubs
		// - with all members
		// - that have not been flagged as expiring
		// - the deadline date is coming up
		$gigs = Gig::with([
			'hub.members' => function($query) {
				$query->where('is_active', '=', true);
			},
			'hub.members.user' => function($query) {
				$query->where('is_active', '=', true);
			},
			// posts will be used to filter out influencers that have already completed the gig
			'posts' => function($query) {
				$query->where('gig_post.status', '<>', 'draft')
					->where('post.author_type', '=', \App\User::class)
					->select('post.*');
			}
		])
			->join('hub', 'hub.id', '=', 'gig.hub_id')
			->where('hub.is_active', '=', true)
			->where('gig.is_active', '=', true)
			->where('gig.has_expiring_notified', '=', false)
			->where('gig.deadline_at', '<=', $cutoff)
			->select([
				'gig.*',
			])
			->get()
			->groupBy('hub_id');

		// send notifications
		foreach($gigs as $hubGroup) {
			$ids = [];
			$summary = [];
			$hub = $hubGroup->first()->hub;

			$this->info('Hub: ' . $hub->name . ' (' . $hub->id . ')');

			foreach($hubGroup as $gig) {
				$this->info('  Gig: ' . $gig->title . ' (' . $gig->id . ')');

				// get recipients
				$recipients = $gig->hub->members->pluck('user');
				$posts = $gig->posts;

				// filter out influencers who have already completed the gig
				$recipients = $recipients->filter(function($item) use ($posts) {
					return !in_array($item->id, $posts->pluck('author_id')->toArray());
				});

				// fire event: event.gig.expiring
				event('event.gig.expiring', ['event' => 'event.gig.expiring', 'gig' => $gig, 'hub' => $hub, 'recipients' => $recipients]);
				$ids[] = $gig->id;
				$summary[] = 'id:' . $gig->id . ' (' . $recipients->count() . ' users)';
			}

			// flag as notified
			Gig::whereIn('id', $ids)->update(['has_expiring_notified' => true]);

			// info
			$this->info('Gigs expiring: ' . implode('; ', $summary));
		}
	}

	/**
	 * Handle gig.expired event
	 */
	private function handleGigExpired()
	{
		$now = carbon();

		// get gigs
		// - active gigs
		// - in active hubs
		// - with hub managers
		// - that have not been flagged as expired
		// - the deadline date is in the past
		$gigs = Gig::with([
			'hub.members' => function($query) {
				$query->where('is_active', '=', true)
					->where('role', '=', 'hubmanager');
			},
			'hub.members.user' => function($query) {
				$query->where('is_active', '=', true);
			},
			// posts will be used to filter out influencers that have already completed the gig
			'posts' => function($query) {
				$query->where('gig_post.status', '<>', 'draft')
					->where('post.author_type', '=', \App\User::class)
					->select('post.*');
			}
		])
			->join('hub', 'hub.id', '=', 'gig.hub_id')
			->where('hub.is_active', '=', true)
			->where('gig.is_active', '=', true)
			->where('gig.has_expired_notified', '=', false)
			->where('gig.deadline_at', '<=', $now)
			->select([
				'gig.*',
			])
			->get()
			->groupBy('hub_id');

		// send notifications
		foreach($gigs as $hubGroup) {
			$ids = [];
			$summary = [];
			$hub = $hubGroup->first()->hub;

			$this->info('Hub: ' . $hub->name . ' (' . $hub->id . ')');

			foreach($hubGroup as $gig) {
				$this->info('  Gig: ' . $gig->title . ' (' . $gig->id . ')');

				// get recipients
				$recipients = $gig->hub->members->pluck('user');
				$posts = $gig->posts;

				// filter out influencers who have already completed the gig
				$recipients = $recipients->filter(function($item) use ($posts) {
					return !in_array($item->id, $posts->pluck('author_id')->toArray());
				});

				// fire event: event.gig.expired
				event('event.gig.expired', ['event' => 'event.gig.expired', 'gig' => $gig, 'hub' => $hub, 'recipients' => $recipients]);
				$ids[] = $gig->id;
				$summary[] = 'id:' . $gig->id . ' (' . $recipients->count() . ' users)';
			}

			// flag as notified
			Gig::whereIn('id', $ids)->update(['has_expired_notified' => true, 'is_live' => false]);

			// info
			$this->info('Gigs expired: ' . implode('; ', $summary));
		}
	}
}
