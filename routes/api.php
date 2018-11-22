<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth
Route::group(['as' => 'auth::', 'namespace' => 'Auth'], function() {

	// auth::login
	Route::get('/login', ['as' => 'login', 'uses' => 'LoginController@getLogin']);

	// auth::logout
	Route::get('/logout', ['as' => 'logout', 'uses' => 'LoginController@logout']);
});

// General
Route::group(['as' => 'general::', 'namespace' => 'General', 'middleware' => 'auth:api'], function() {

	// general::login.store
	Route::post('/login/store', ['as'=> 'login.store', 'uses' => 'AuthController@storeLogin']);

	// general::login.mobile
	Route::post('/login/mobile', ['as'=> 'login.mobile', 'uses' => 'AuthController@mobileLogin']);

	// general::onesignal.verify
	Route::get('/onesignal/verify', ['as'=> 'onesignal.verify', 'uses' => 'AuthController@verifiyIdentity']);

	// general::entity
	Route::get('/entity', ['as'=> 'entity', 'uses' => 'AuthController@getEntity']);

	// general::hub.select
	Route::get('/hub/select/{hub}', ['as' => 'hub.select', 'uses' => 'HubController@selectHub']);

	// general::hub.list
	Route::get('/hub/list', ['as' => 'hub.list', 'uses' => 'HubController@getList']);


	// attachments

	// general::attachment.upload
	Route::post('/attachment/upload', ['as' => 'attachment.upload', 'uses' => 'AttachmentController@fileUpload']);

	// general::attachment.scrape
	Route::post('/attachment/scrape', ['as' => 'attachment.scrape', 'uses' => 'AttachmentController@fileScrape']);

	// general::attachment.copy
	Route::post('/attachment/copy', ['as' => 'attachment.copy', 'uses' => 'AttachmentController@fileCopy']);
});

// Hub (separated middleware)

Route::group(['as' => 'hub::', 'namespace' => 'Hub', 'prefix' => '{hub}', 'middleware' => ['client_credentials']], function() {

	// hub::account-submit
	Route::post('/account-setup/{membership}', ['as' => 'account-submit', 'uses' => 'SettingsController@accountSubmit']);
});

// Hub
Route::group(['as' => 'hub::', 'namespace' => 'Hub', 'prefix' => '{hub}', 'middleware' => ['auth:api', 'hub.member']], function() {

	// entity

	// hub::entity.search
	Route::get('/entity/search', ['as' => 'entity.search', 'uses' => 'UserController@searchEntity']);


	// posts

	// hub::post.write
	Route::get('/post/write', ['as' => 'post.write', 'uses' => 'PostController@write']);

	// hub::post.create
	Route::post('/post/write', ['as' => 'post.create', 'uses' => 'PostController@create']);

	// hub::post.feed
	Route::get('/newsfeed', ['as' => 'post.feed', 'uses' => 'PostController@getFeed']);

	// hub::post.view
	Route::get('/post/{post}', ['as' => 'post.view', 'uses' => 'PostController@getPost']);

	// hub::post.shares
	Route::get('/post/{post}/shares', ['as' => 'post.shares', 'uses' => 'PostController@getSharesList']);

	// hub::post.instagram
	Route::get('/post/{post}/instagram/{item}', ['as' => 'post.instagram', 'uses' => 'PostController@getInstagramPost']);

	// hub::post.instagram.sharing
	Route::post('/post/{post}/instagram/{item}', ['as' => 'post.instagram.sharing', 'uses' => 'PostController@instagramPostSharing']);

	// hub::post.comment
	Route::post('/post/{post}', ['as' => 'post.comment', 'uses' => 'PostController@comment']);

	// hub::post.like
	Route::post('/post/{post}/like', ['as' => 'post.comment', 'uses' => 'PostController@like']);

	// hub::post.tag
	Route::get('/post/tag/{account?}', ['as' => 'tag', 'uses' => 'PostController@tag']);

	// hub::post.toggle-hidden
	Route::post('/post/{post}/toggle-hidden', ['as' => 'toggle-hidden', 'uses' => 'PostController@toggleHidden']);

	// hub::post.report
	Route::post('/post/{post}/report', ['as' => 'report', 'uses' => 'PostController@report']);

	// hub::post.unpublish
	Route::post('/post/{post}/unpublish', ['as' => 'unpublish', 'uses' => 'PostController@unpublish']);


	// gigs

	// hub::gig.my.scheduled
	Route::get('/mygigs/scheduled', ['as' => 'gig.my.scheduled', 'uses' => 'GigController@getScheduled']);

	// hub::gig.my.count
	Route::get('/mygigs/count', ['as' => 'gig.my.count','uses' => 'GigController@getTotalMyGigCount']);

	// TODO: move this to general::
	// hub::gig.my.platforms
	Route::get('/mygigs/platforms', ['as' => 'gig.my.platforms', 'uses' => 'GigController@getPlatforms']);

	// hub::gig.my.scheduled.cancel
	Route::post('/mygigs/scheduled/{post}/cancel', ['as' => 'gig.my.scheduled.cancel','uses' => 'GigController@cancelPost']);

	// hub::gig.my.scheduled.reschedule
	Route::post('/mygigs/scheduled/{post}/reschedule', ['as' => 'gig.my.scheduled.reschedule','uses' => 'GigController@reschedulePost']);

	// hub::gig.my.rejected
	Route::get('/mygigs/rejected', ['as' => 'gig.my.rejected', 'uses' => 'GigController@getRejected']);

	// hub::gig.my.approval
	Route::get('/mygigs/approval', ['as' => 'gig.my.approval', 'uses' => 'GigController@getApproval']);

	// hub::gig.my.feed
	Route::get('/mygigs/feed', ['as' => 'gig.my.feed', 'uses' => 'GigController@getGigFeeds']);

	// hub::gig.my.feed.post
	Route::post('/mygigs/feed/post/{feed_post}', ['as' => 'gig.my.feed.post', 'uses' => 'GigController@createFeedPostContext']);

	// hub::gig.my.feed.manage
	Route::get('/mygigs/feed/manage', ['as' => 'gig.my.feed.manage', 'uses' => 'GigController@getFeedConfigList']);

	// hub::gig.my.feed.manage.edit
	Route::get('/mygigs/feed/manage/edit/{gig_feed}', ['as' => 'gig.my.feed.manage.edit', 'uses' => 'GigController@getFeedConfig']);

	// hub::gig.my.feed.manage.update
	Route::patch('/mygigs/feed/manage/edit/{gig_feed}', ['as' => 'gig.my.feed.manage.update', 'uses' => 'GigController@updateFeedConfig']);

	// hub::gig.my.feed.validate
	Route::post('/mygigs/feed/validate', ['as' => 'gig.my.feed.validate', 'uses' => 'GigController@validateRssLink']);

	// hub::gig.my.feed.manage.store
	Route::post('/mygigs/feed/manage', ['as' => 'gig.my.feed.manage.store', 'uses' => 'GigController@createFeedConfig']);

	// hub::gig.create
	Route::get('/gig/create', ['as' => 'gig.create', 'uses' => 'GigController@getCreate']);

	// hub::gig.store
	Route::post('/gig/create', ['as' => 'gig.store', 'uses' => 'GigController@postCreate']);

	// hub::gig.edit
	Route::get('/gig/edit/{gig}', ['as' => 'gig.edit', 'uses' => 'GigController@getEdit']);

	// hub::gig.update
	Route::patch('/gig/edit/{gig}', ['as' => 'gig.update', 'uses' => 'GigController@update']);

	// hub::gig.delete.attachment
	Route::delete('/gig/{gig}/remove/attachment/{attachment}', ['as' => 'gig.delete.attachment', 'uses' => 'GigController@deleteAttachment']);

	// hub::gig.delete.reward
	Route::delete('/gig/{gig}/remove/reward/{reward}', ['as' => 'gig.delete.reward', 'uses' => 'GigController@deleteReward']);

	// hub::gig.review
	Route::get('/gig/review', ['as' => 'gig.review', 'uses' => 'GigController@getReview']);

	// hub::gig.accept
	Route::post('/gig/accept', ['as' => 'gig.accept', 'uses' => 'GigController@postAccept']);

	// hub::gig.reject
	Route::post('/gig/reject', ['as' => 'gig.reject', 'uses' => 'GigController@postReject']);

	// hub::gig.ignore
	Route::get('/gig/ignore/{gig}', ['as' => 'gig.ignore', 'uses' => 'GigController@ignore']);

	// hub::gig.list
	Route::get('/gigs', ['as' => 'gig.list', 'uses' => 'GigController@getList']);

	// hub::gig.list.expired
	Route::get('/gigs/expired', ['as' => 'gig.list.expired', 'uses' => 'GigController@getExpired']);

	// hub::gig.view
	Route::get('/gig/{gig}', ['as' => 'gig.view', 'uses' => 'GigController@getGig']);

	// hub::gig.delete
	Route::delete('/gig/{gig}', ['as' => 'gig.delete', 'uses' => 'GigController@delete']);


	// messages

	// hub::message.inbox
	Route::get('/message/inbox', ['as' => 'message.inbox', 'uses' => 'MessageController@getConversations']);

	// hub::message.conversation
	Route::get('/conversation/{conversation}', ['as' => 'message.conversation', 'uses' => 'MessageController@getConversation']);

	// hub::message.append
	Route::post('/conversation/{conversation}', ['as' => 'message.append', 'uses' => 'MessageController@postMessage']);

	// hub::message.write
	Route::get('/conversation/new/{entity}', ['as' => 'message.write', 'uses' => 'MessageController@getWrite']);

	// hub::message.new
	Route::post('/conversation/new/{entity}', ['as' => 'message.new', 'uses' => 'MessageController@postConversation']);

	// hub::message.notifications
	Route::get('/message/notifications', ['as' => 'message.notifications', 'uses' => 'MessageController@getNotifications']);

	// hub::message.notification
	Route::get('/notification/{notification}', ['as' => 'message.notification', 'uses' => 'MessageController@getNotification']);


	// leaderboard

	// hub::leaderboard
	Route::get('/leaderboard', ['as' => 'leaderboard', 'uses' => 'LeaderboardController@getLeaderboard']);


	// settings

	// hub::settings.delete.category
	Route::delete('/settings/remove/category/{category}', ['as' => 'settings.delete.category', 'uses' => 'SettingsController@deleteCategory']);

	// hub::settings.influencers
	Route::get('/settings/influencers', ['as' => 'settings.influencers', 'uses' => 'SettingsController@getInfluencersList']);

	// hub::settings.influencers.remove
	Route::post('/settings/influencers/remove', ['as' => 'settings.influencers.remove', 'uses' => 'SettingsController@removeInfluencers']);

	// hub::settings.export
	Route::get('/settings/export', ['as' => 'settings.export', 'uses' => 'SettingsController@exportInfluencers']);

	// hub::settings.custom-fields.update
	Route::post('/settings/custom-fields', ['as' => 'settings.custom-fields.update', 'uses' => 'SettingsController@updateCustomFields']);

	// hub::settings.membership.groups
	Route::get('/settings/membership/groups', ['as' => 'settings.membership.groups', 'uses' => 'SettingsController@getMembershipGroups']);

	// hub::settings.membership.groups.create
	Route::post('/settings/membership/groups', ['as' => 'settings.membership.groups.create', 'uses' => 'SettingsController@postMembershipGroups']);
	
	// hub::settings.membership.groups.set
	Route::post('/settings/membership/groups/set', ['as' => 'settings.membership.groups.set', 'uses' => 'SettingsController@setGroup']);
	
	// hub::settings.membership.groups.delete
	Route::delete('/settings/membership/groups/{group}', ['as' => 'settings.membership.groups.delete', 'uses' => 'SettingsController@deleteMembershipGroup']);
	
	// hub::settings.reset.points
	Route::post('/settings/influencers/reset', ['as' => 'settings.influencers.reset-points', 'uses' => 'SettingsController@resetPoints']);

	// hub::settings.delete.category
	Route::post('/settings/influencers/invite', ['as' => 'settings.influencers.invite', 'uses' => 'SettingsController@inviteInfluencers']);
	
	// hub::settings.linked-account
	Route::post('/settings/linked-account', ['as' => 'settings.linked-account', 'uses' => 'SettingsController@updateLinkedAccounts']);

	// hub::settings
	Route::get('/settings/{tab?}', ['as' => 'settings', 'uses' => 'SettingsController@getSettings']);

	// hub::settings.update
	Route::post('/settings/{tab?}', ['as' => 'settings.update', 'uses' => 'SettingsController@postSettings']);

	// user

	// hub::hub.profile
	Route::get('/about', ['as' => 'hub.profile', 'uses' => 'HubController@getProfile']);

	// hub::user.profile
	Route::get('/{user}', ['as' => 'user.profile', 'uses' => 'UserController@getProfile']);

	// reporting
	Route::group(['prefix' => 'reporting'], function() {
		// hub::history
		Route::get('/history', ['as' => 'history', 'uses' => 'ReportingController@history']);
		
		// hub::reporting.gig
		Route::get('/{screen}', ['as' => 'reporting.gig', 'uses' => 'ReportingController@getReport']);

		// hub::reporting.preview
		Route::get('/preview/{screen}', ['as' => 'reporting.preview', 'uses' => 'ReportingController@export']);
	});

	// term and condition

	// hub::user.profile
	Route::post('/terms/accept', ['as' => 'terms.accept', 'uses' => 'SettingsController@acceptTerms']);
});