<?php

namespace App\Http\Controllers\Hub;

// App
use App\Events\Conversation\MessageSent;
use App\Events\Conversation\NewMessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\SendMessageRequest;
use App\Conversation;
use App\Entity;
use App\Hub;
use App\Notification;
use App\User;

// Laravel
use Illuminate\Http\Request;

class MessageController extends Controller
{
	/**
	 * GET /api/{hub}/message/inbox
	 * ROUTE hub::message.inbox [api.php]
	 *
	 * The hub messages list page (inbox tab)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getConversations(Request $request, Hub $hub)
	{
		// auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get behalf based on auth user
		$behalf = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$behalf = $hub;
		}

		// conversations
		// - active conversations
		// - in current hub
		// - engaged by current user (or hub)
		// - show most recent message on top
		$conversations = Conversation::with([
				'sender',
				'receiver',
			])
			->join('message', 'conversation.last_message_id', '=', 'message.id')
			->where('conversation.hub_id', '=', $hub->id)
			->where(function($query) use ($behalf) {
				$query->where(function($query) use ($behalf) {
					$query->where('conversation.sender_id', '=', $behalf->id)
						->where('conversation.sender_type', '=', get_class($behalf));
				})->orWhere(function($query) use ($behalf) {
					$query->where('conversation.receiver_id', '=', $behalf->id)
						->where('conversation.receiver_type', '=', get_class($behalf));
				});
			})
			->select([
				'conversation.id', 
				'conversation.sender_id', 
				'conversation.sender_type', 
				'conversation.receiver_id', 
				'conversation.receiver_type',
				'message.message',
				'message.created_at'
			])
			->orderBy('message.created_at', 'DESC')
			->get();

		// response
		$data = [
			'data' => [
				'conversations' => $conversations->toArray()
			],
			'route' => 'hub::message.inbox',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/message/write/new/{entity}
	 * ROUTE hub::message.write [api.php]
	 *
	 * The hub message write page
	 *
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \App\Hub                   $hub
	 * @param  \App\Entity                $entity
	 * @return \Illuminate\Http\Response
	 */
	public function getWrite(Request $request, Hub $hub, Entity $entity)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();
		// checkpoint: entity exists
		if(is_null($entity)) {
			return response()->redirectToRoute('hub::message.inbox', ['hub' => $hub->id]);
		}

		// get sender behalf based on auth user
		$sender = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$sender = $hub;
		}

		// get other entity
		$receiver = $entity;

		// get conversation
		// if it exists, then we'll do a redirect
		$conversation = Conversation::query()
			->where('conversation.hub_id', '=', $hub->id)
			->where(function($query) use ($sender, $receiver) {
				$query->where(function($query) use ($sender, $receiver) {
					$query
						->where('conversation.receiver_id', '=', $receiver->entity_id)
						->where('conversation.receiver_type', '=', $receiver->entity_type)
						->where('conversation.sender_id', '=', $sender->id)
						->where('conversation.sender_type', '=', get_class($sender));
				})->orWhere(function($query) use ($sender, $receiver) {
					$query
						->where('conversation.receiver_id', '=', $sender->id)
						->where('conversation.receiver_type', '=', get_class($sender))
						->where('conversation.sender_id', '=', $receiver->entity_id)
						->where('conversation.sender_type', '=', $receiver->entity_type);
				});
			})
			->first(); // expecting zero or one records

		// conversation found: redirect to conversation
		if(!is_null($conversation)) {
			return response()->redirectToRoute('hub::message.conversation', [
				'hub' => $hub->slug, 
				'conversation' => $conversation->id
			]);
		}

		// response
		return response()->json([
			'data' => [
				'receiver' => $receiver->original
			],
			'route' => 'hub::message.write',
			'success' => true
		]);
	}

	/**
	 * POST /api/{hub}/conversation/new/{entity}
	 * ROUTE hub::message.new [api.php]
	 *
	 * The hub new message endpoint
	 *
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \App\Hub                   $hub
	 * @param  \App\Entity                $entity
	 * @return \Illuminate\Http\Response
	 */
	public function postConversation(SendMessageRequest $request, Hub $hub, Entity $entity)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// checkpoint: entity doesn't exists
		if(is_null($entity)) {
			return response()->redirectToRoute('hub::message.inbox', ['hub' => $hub->id]);
		}

		// get behalf based on auth user
		$sender = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$sender = $hub;
		}

		// get other entity
		$receiver = $entity;

		// create new conversation
		$conversation = Conversation::create(['hub_id' => $hub->id]);
		$conversation->sender()->associate($sender);
		// get the original instead of the entity itself
		$conversation->receiver()->associate($receiver->original); 
		$conversation->save();

		// create new message in conversation
		$message = $conversation->messages()->create($request->only('message'));
		$message->sender()->associate($sender);
		$message->save();

		// broadcast event
		broadcast(new NewMessageSent($conversation))->toOthers();

		// response
		$data = [
			'data' => [
				'conversation' => $conversation->toArray()
			],
			'route' => 'hub::message.new',
			'success' => true
		];
		return response()->json($data);
	}

	/*
	 * GET /api/{hub}/conversation/{conversation}
	 * ROUTE hub::message.conversation [api.php]
	 *
	 * The hub conversation page
	 *
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \App\Hub                   $hub
	 * @param  \App\Conversation          $conversation
	 * @return \Illuminate\Http\Response
	 */
	public function getConversation(Request $request, Hub $hub, Conversation $conversation)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// @todo: policy for conversation - auth user has access

		// get conversation participants
		$conversation->load([
			'sender',
			'receiver'
		]);

		// get messages
		$messages = $conversation->messages()->with([
			'sender',
		])
			->latest()
			->paginate(20);

		// response
		$data = [
			'data' => [
				'conversation' => $conversation->toArray(),
				'messages' => $messages->toArray()
			],
			'route' => 'hub::message.conversation',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * POST /api/{hub}/conversation/{conversation}
	 * ROUTE hub::message.append [api.php]
	 *
	 * The hub send message endpoint
	 *
	 * @param  \Illuminate\Http\Request   $request
	 * @param  \App\Hub                   $hub
	 * @param  \App\Conversation          $conversation
	 * @return \Illuminate\Http\Response
	 */
	public function postMessage(SendMessageRequest $request, Hub $hub, Conversation $conversation)
	{
		// get auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get behalf based on auth user
		$sender = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$sender = $hub;
		}

		// create new message
		$message = $conversation->messages()->create($request->only('message'));
		$message->sender()->associate($sender);
		$message->save();

		// broadcast event
		broadcast(new MessageSent($message))->toOthers();

		// response
		$data = [
			'data' => [
				'message' => $message->toArray()
			],
			'route' => 'hub::message.send', 
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/message/notifications
	 * ROUTE hub::message.notifications [api.php]
	 *
	 * The hub messages list page (notifications tab)
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @return \Illuminate\Http\Response
	 */
	public function getNotifications(Request $request, Hub $hub)
	{
		// auth user
		$auth_user = isset($request->auth_user) ? $request->auth_user : auth()->user();

		// get behalf based on auth user
		$behalf = $auth_user;
		if($auth_user->getMembershipTo($hub)->role == 'hubmanager') {
			$behalf = $hub;
		}

		// notification
		$notifications = Notification::with([
			'sender'
		])
			->where('notification.hub_id', '=', $hub->id)
			->where('notification.receiver_id', '=', $behalf->id)
			->where('notification.receiver_type', '=', get_class($behalf))
			->orderBy('notification.created_at', 'DESC')
			->get();

		// response
		$data = [
			'data' => [
				'notifications' => $notifications->toArray()
			],
			'route' => 'hub::message.notifications',
			'success' => true
		];
		return response()->json($data);
	}

	/**
	 * GET /api/{hub}/notification/{notification}
	 * ROUTE hub::message.notification [api.php]
	 *
	 * The hub notification
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \App\Hub                  $hub
	 * @param  \App\Notification         $notification
	 * @return \Illuminate\Http\Response
	 */
	public function getNotification(Request $request, Hub $hub, Notification $notification)
	{
		//$user = $request->user();

		return response()->json(['route' => 'hub::message.notification', 'success' => true]);
	}
}
