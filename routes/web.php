<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Sandpit
Route::get('/sandpit/{method?}', ['as' => 'test::sandpit', 'uses' => 'TestController@index']);
Route::post('/sandpit/{method?}', ['as' => 'test::sandpit.post', 'uses' => 'TestController@index']);


// Hub (no auth)
Route::group(['as' => 'hub::', 'namespace' => 'Hub', 'prefix' => '{hub}'], function() {
	
	// alerts

	// hub::alert.read
	Route::get('/a/{alert}', ['as' => 'alert.read', 'uses' => 'AlertController@getAlert']);

	// hub::alert.gig
	Route::get('/a/g/{alert}/{gig}', ['as' => 'alert.gig', 'uses' => 'AlertController@getReadAlertGig']);

	// hub::post.write
	// @todo: Need to remove this later
	Route::get('/post/write', ['as' => 'post.write', 'uses' => 'PostController@write']);

	// hub::account-setup
	Route::get('/account-setup/{membership}', ['as' => 'account-setup', 'uses' => 'SettingsController@accountSetup']);

	// hub::report.preview 
	Route::get('/report/preview/{screen}', ['as' => 'report.preview', 'uses' => 'ReportingController@getReport']); 
});

// SPA routes that matches the front-end route
// @note: it is causing the app: ERR_TOO_MANY_REDIRECTS response.
// Route::group(['namespace' => 'General'], function() {

// 	// we might have a route that don't need the hub.

// 	Route::group(['as' => 'hub::', 'prefix' => '{hub}'], function() {
		
// 		// posts

// 		// hub::post.view
// 		Route::get('/post/{post}', ['as' => 'post.view', 'uses' => 'SPAController@postView']);
// 	});
// });

// Master
Route::group(['as' => 'master::', 'namespace' => 'Master', 'prefix' => 'master', 'middleware' => ['auth.master'/*'auth', 'master.member'*/]], function() {

	// master::hub.master
	Route::get('/', ['as' => 'hub.master', 'uses' => 'DashboardController@index']);

	// Master - Hubs
	Route::group(['prefix' => 'hubs'], function() {

		// master::hub.index
		Route::get('/', ['as' => 'hub.index', 'uses' => 'HubController@index']);

		// master::hub.create
		Route::get('/create', ['as' => 'hub.create', 'uses' => 'HubController@create']);

		// master::hub.store
		Route::post('/create', ['as' => 'hub.store', 'uses' => 'HubController@store']);

		// master::hub.edit
		Route::get('/edit/{hub}', ['as' => 'hub.edit', 'uses' => 'HubController@edit']);

		// master::hub.update
		Route::put('/edit/{hub}', ['as' => 'hub.update', 'uses' => 'HubController@update']);

		// master::hub.memberaction
		Route::put('/edit/{hub}/action', ['as' => 'hub.memberaction', 'uses' => 'HubController@memberAction']);

		// master::hub.setmanager
		Route::put('/edit/{hub}/setmanager', ['as' => 'hub.setmanager', 'uses' => 'HubController@setmanager']);

		// master::hub.action
		Route::post('/action', ['as' => 'hub.action', 'uses' => 'HubController@action']);

		// master::hub.destroy
		Route::delete('/{hub}', ['as' => 'hub.destroy', 'uses' => 'HubController@destroy']);
	});

	// Master - Users
	Route::group(['prefix' => 'users'], function() {

		// master::user.index
		Route::get('/', ['as' => 'user.index', 'uses' => 'UserController@index']);

		// master::user.create
		Route::get('/create', ['as' => 'user.create', 'uses' => 'UserController@create']);

		// master::user.store
		Route::post('/create', ['as' => 'user.store', 'uses' => 'UserController@store']);

		// master::user.edit
		Route::get('/edit/{user}', ['as' => 'user.edit', 'uses' => 'UserController@edit']);

		// master::user.update
		Route::put('/edit/{user}', ['as' => 'user.update', 'uses' => 'UserController@update']);

		// master::user.action
		Route::put('/action', ['as' => 'user.action', 'uses' => 'UserController@action']);
		
		// master::user.destroy
		Route::delete('/{user}', ['as' => 'user.destroy', 'uses' => 'UserController@destroy']);

	});

	// Master - Staffs
	Route::group(['prefix' => 'staffs'], function() {

		// master::staff.index
		Route::get('/', ['as' => 'staff.index', 'uses' => 'StaffController@index']);

		// master::staff.create
		Route::get('/create', ['as' => 'staff.create', 'uses' => 'StaffController@create']);

		// master::staff.create
		Route::post('/create', ['as' => 'staff.store', 'uses' => 'StaffController@store']);

		// master::staff.edit
		Route::get('/edit/{staff}', ['as' => 'staff.edit', 'uses' => 'StaffController@edit']);

		// master::staff.update
		Route::put('/edit/{staff}', ['as' => 'staff.update', 'uses' => 'StaffController@update']);

		// master::staff.action
		Route::put('/action', ['as' => 'staff.action', 'uses' => 'StaffController@action']);
	});

	// Master - Packages
	Route::group(['prefix' => 'packages'], function() {

		// master::package.index
		Route::get('/', ['as' => 'package.index', 'uses' => 'PackageController@index']);
	});

	// Master - Logs
	Route::group(['prefix' => 'logs'], function() {

		// master::logs
		Route::get('/', ['as' => 'log.index', 'uses' => 'LogController@index']);

		// master::logs
		Route::get('/{logtype}', ['as' => 'log.type', 'uses' => 'LogController@getLogs']);
	});
});

// General
Route::group(['as' => 'general::', 'namespace' => 'General'], function() {
	
	// thumbnailing and images
	
	// general::thumbnail
	Route::get('/thumb/{template}/{file_path}', ['as' => 'thumbnail', 'uses' => 'ImageController@thumbnail'])
	->where('file_path', '.*');
	
	// general::avatar
	Route::get('/avatar/{xc}/{yc}/{wc}/{hc}/{wf}/{hf}/{file_path}', ['as' => 'avatar', 'uses' => 'ImageController@avatar'])
	->where('file_path', '.*');
	
	// general::process.image
	Route::get('/safe-image', ['as' => 'process.image', 'uses' => 'ImageController@safeImage']);
	
	// small helpers
	// general::account.loggedin
	Route::get('/account-loggedin', ['as' => 'account.loggedin', 'uses' => 'AuthController@loggedin']);

	// general::check-slug
	Route::get('/check-slug', ['as' => 'check-slug', 'uses' => 'AuthController@checkSlug']);
		
	// general::reset-password
	Route::post('/reset-password', ['as' => 'reset-password', 'uses' => 'AuthController@passwordReset']);

	// general::send-reset-link
	Route::post('/send-password', ['as' => 'send-reset-link', 'uses' => 'AuthController@sendPasswordLink']);

	// integrations

	// general::social.provider
	Route::get('/social/{provider}', ['as' => 'social.provider', 'uses' => 'SocialController@provider']);

	// general::social.callback
	Route::get('/social/{provider}/callback', ['as' => 'social.callback', 'uses' => 'SocialController@callback']);

	// general::handle-link
	Route::get('/handle-link', ['as' => 'handle-link', 'uses' => 'HomeController@handleLink']);

	// catch all

	// general::entry
	Route::get('/{catch_all?}', ['as' => 'entry', 'uses' => 'HomeController@entry'])
		->where('catch_all', '.*'); // catch all in /

});

