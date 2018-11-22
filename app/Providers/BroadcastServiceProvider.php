<?php

namespace App\Providers;

// App
use App\Conversation;
use App\User;
use App\Hub;
use App\Membership;

// Laravel
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		// redirects 
		Broadcast::routes(['middleware' => 'auth:api']);

		// channel: Conversation.*
		// authorize auth user if they have access to conversation
		// note: user can belong to multiple hub
		Broadcast::channel('Conversation.*', function($user, $conversationId) {
			$entityIds = collect([$user->id]); // convert to array so we can use whereIn method

			// if the user membership's role is a hubmanager, 
			// then transform the entity to \App\Hub instance
			$membership = Membership::join('hub', 'hub.id', '=', 'membership.hub_id')
				->where('membership.user_id', '=', $user->id)
				->where('membership.role', '=', 'hubmanager')
				->where('membership.is_active', '=', true)
				->select(['hub.id as hub_id'])
				->get();
			
			if ($membership->count()) {
				$entityIds = $membership->keyBy('hub_id')->keys();
			}

			$entityIds = $entityIds->all(); // get the underlying array

			// check if entity have access to conversation
			$conversation = Conversation::find($conversationId);

			logger($conversation); 
			
			return $conversation->canAccessBy($entityIds);
		});

		// authorize user if he can receive new conversations
		// NOTE: all $receiverId must be an \App\User instance, 
		// so we're passing the user.orignal.id if it's a hub instead of just user.id
		// channel: ConversationNew.*
		Broadcast::channel('ConversationNew.*', function($user, $receiverId) {
			return $user->id == $receiverId;
		});

		Broadcast::channel('SocialAccount.*', function($user, $receiverId) {
			return $user->id == $receiverId;
		});

		Broadcast::channel('PushNotification.*', function($user, $receiverId) {
			return $user->id == $receiverId;
		});
	}
}
