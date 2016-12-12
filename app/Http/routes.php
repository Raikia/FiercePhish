<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
// Template: https://colorlib.com/polygon/gentelella/icons.html

// Authentication Routes...
Route::get('login', 'Auth\AuthController@showLoginForm');
Route::post('login', 'Auth\AuthController@login');
Route::get('logout', 'Auth\AuthController@logout');



// TargetsController Routes...
Route::get('targets', 'TargetsController@index');
Route::post('targets/add', 'TargetsController@addTarget');
Route::post('targets/import', 'TargetsController@importTargets');
Route::get('targets/lists', 'TargetsController@targetlists_index');
Route::post('targets/lists/add', 'TargetsController@addList');
Route::get('targets/assign/{id?}', 'TargetsController@assign_index');
Route::post('targets/assign/set', 'TargetsController@assignToLists');



// CampaignController Routes...
Route::get('campaigns', 'CampaignController@index');
Route::get('campaigns/create', 'CampaignController@create');
Route::post('campaigns/create', 'CampaignController@create_post');
Route::get('campaigns/{id}', 'CampaignController@campaign_details');
Route::post('campaigns/{id}/cancel', 'CampaignController@campaign_cancel');



// SettingsController Routes...
Route::get('settings/users', 'SettingsController@index');
Route::post('settings/users/add', 'SettingsController@addUser');
Route::post('settings/users/delete', 'SettingsController@deleteUser');
Route::get('settings/profile/{id?}', 'SettingsController@get_editprofile');
Route::post('settings/profile', 'SettingsController@post_editprofile');
Route::get('settings/config', 'SettingsController@get_config');
Route::post('settings/config/save', 'SettingsController@post_config');


// EmailController Routes...
Route::get('emails/templates/{id?}', 'EmailController@template_index');
Route::post('emails/templates/add', 'EmailController@addTemplate');
Route::post('emails/templates/edit', 'EmailController@editTemplate');
Route::post('emails/templates/delete', 'EmailController@deleteTemplate');
Route::get('emails/check', 'EmailController@check_settings_index');
Route::get('emails/simple', 'EmailController@send_simple_index');
Route::post('emails/simple/send', 'EmailController@send_simple_post');

// Dashboard Routes...
Route::get('home', 'DashboardController@index');
Route::get('/', 'DashboardController@index');



// Ajax Routes...
Route::post('ajax/targetuser/note', 'AjaxController@edit_targetuser_notes');
Route::post('ajax/targetlist/note', 'AjaxController@edit_targetlist_notes');
Route::get('ajax/emails/template/{id?}', 'AjaxController@get_emailtemplate_info');
Route::get('ajax/emails/check/{command?}/{domain?}', 'AjaxController@email_check_commands');