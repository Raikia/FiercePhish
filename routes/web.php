<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

// Force URL if a proxy is present
if (!empty(config('fiercephish.PROXY_URL')))
	URL::forceRootUrl(config('fiercephish.PROXY_URL'));
if (!empty(config('fiercephish.PROXY_SCHEMA')))
	URL::forceScheme(config('fiercephish.PROXY_SCHEMA'));


Route::group(['prefix' => config('fiercephish.URI_PREFIX')], function() {
	// Authentication Routes...
	Route::get('login', 'Auth\LoginController@showLoginForm');
	Route::post('login', 'Auth\LoginController@login');
	Route::get('logout', 'Auth\LoginController@logout');
	Route::get('2fa/validate', 'Auth\LoginController@getValidateToken');
	Route::post('2fa/validate', ['middleware' => 'throttle:5', 'uses' => 'Auth\LoginController@postValidateToken']);



	// TargetsController Routes...
	Route::get('targets', 'TargetsController@index');
	Route::post('targets/add', 'TargetsController@addTarget');
	Route::post('targets/import', 'TargetsController@importTargets');
	Route::get('targets/lists', 'TargetsController@targetlists_index');
	Route::get('targets/list/{id}', 'TargetsController@targetlists_details');
	Route::post('targets/lists/add', 'TargetsController@addList');
	Route::post('targets/list/{id}/clear', 'TargetsController@clearList');
	Route::post('targets/list/{id}/addall', 'TargetsController@addAlltoList');
	Route::post('targets/list/{id}/addrandom', 'TargetsController@addRandomtoList');
	Route::post('targets/list/removeuser/{id?}/{user_id?}', 'TargetsController@removeUser');
	Route::get('targets/assign/{id}', 'TargetsController@assign_index');
	Route::post('targets/assign/set', 'TargetsController@assignToLists');



	// CampaignController Routes...
	Route::get('campaigns', 'CampaignController@index');
	Route::get('campaigns/create', 'CampaignController@create');
	Route::post('campaigns/create', 'CampaignController@create_post');
	Route::get('campaigns/{id?}', 'CampaignController@campaign_details');
	Route::post('campaigns/{id}/cancel', 'CampaignController@campaign_cancel');



	// SettingsController Routes...
	Route::get('settings/users', 'SettingsController@index');
	Route::post('settings/users/add', 'SettingsController@addUser');
	Route::post('settings/users/delete', 'SettingsController@deleteUser');
	Route::get('settings/profile/{id?}', 'SettingsController@get_editprofile');
	Route::post('settings/profile', 'SettingsController@post_editprofile');
	Route::post('2fa/enable', 'Google2FAController@enableTwoFactor');
	Route::post('2fa/disable', 'Google2FAController@disableTwoFactor');
	Route::get('settings/config', 'SettingsController@get_config');
	Route::post('settings/config/save', 'SettingsController@post_config');
	Route::get('settings/export', 'SettingsController@get_import_export');
	Route::post('settings/export/download', 'SettingsController@post_export_data');
	Route::get('settings/export/download', 'SettingsController@post_export_data');
	Route::post('settings/export/import', 'SettingsController@post_import_data');

	// LogController Routes...
	Route::get('logs', 'LogController@index');
	Route::get('logs/download/{type}', 'LogController@download');
	
	// EmailController Routes...
	Route::get('emails/templates/{id?}', 'EmailController@template_index');
	Route::post('emails/templates/add', 'EmailController@addTemplate');
	Route::post('emails/templates/edit', 'EmailController@editTemplate');
	Route::post('emails/templates/delete', 'EmailController@deleteTemplate');
	Route::get('emails/check', 'EmailController@check_settings_index');
	Route::get('emails/simple/{id?}/{fwd?}', 'EmailController@send_simple_index');
	Route::post('emails/simple/send', 'EmailController@send_simple_post');
	Route::get('emails/log', 'EmailController@email_log');
	Route::get('emails/log/{id?}', 'EmailController@email_log_details');
	Route::post('emails/log/{id}/resend', 'EmailController@email_resend');
	Route::post('emails/log/{id}/cancel', 'EmailController@email_cancel');
	Route::get('inbox', 'EmailController@inbox_get');
	Route::get('inbox/download/{id?}', 'EmailController@inbox_download_attachment');
	
	
	// Dashboard Routes...
	Route::get('home', 'DashboardController@index');
	Route::get('/', 'DashboardController@index');



	// Ajax Routes...
	Route::post('ajax/targetuser/note', 'AjaxController@edit_targetuser_notes');
	Route::post('ajax/targetlist/note', 'AjaxController@edit_targetlist_notes');
	Route::post('ajax/targetuser/list/{id?}', 'AjaxController@targetuser_list');
	Route::post('ajax/targetlist/membership/{id}', 'AjaxController@targetuser_membership');
	Route::get('ajax/targetlist/membership/{id}', 'AjaxController@targetuser_membership');
	Route::get('ajax/emails/template/{id?}', 'AjaxController@get_emailtemplate_info');
	Route::get('ajax/emails/check/{command?}/{domain?}', 'AjaxController@email_check_commands');
	Route::post('ajax/email/log', 'AjaxController@email_log');
	Route::get('ajax/log/{id?}', 'AjaxController@get_activitylog');
	Route::get('ajax/jobs', 'AjaxController@get_jobs');
	Route::post('ajax/campaign/{id}', 'AjaxController@campaign_emails_get');
	Route::get('ajax/inbox/new', 'AjaxController@get_num_new_messages');
	Route::get('ajax/inbox/delete/{id?}', 'AjaxController@delete_inbox_message');
	Route::get('ajax/inbox/{id?}', 'AjaxController@get_inbox_messages');
});