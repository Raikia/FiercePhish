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


// SettingsController Routes...
Route::get('settings/users', 'SettingsController@index');
Route::post('settings/users/add', 'SettingsController@addUser');
Route::post('settings/users/delete', 'SettingsController@deleteUser');
Route::get('settings/profile/{id?}', 'SettingsController@get_editprofile');
Route::post('settings/profile', 'SettingsController@post_editprofile');


// EmailController Routes...
Route::get('emails/templates/{id?}', 'EmailController@template_index');
Route::post('emails/templates/add', 'EmailController@addTemplate');
Route::post('emails/templates/edit', 'EmailController@editTemplate');
Route::get('emails/check', 'EmailController@check_settings_index');

// Dashboard Routes...
Route::get('home', 'DashboardController@index');
Route::get('/', 'DashboardController@index');



// Ajax Routes...
Route::post('ajax/targetuser/note', 'AjaxController@edit_targetuser_notes');
Route::post('ajax/targetlist/note', 'AjaxController@edit_targetlist_notes');
Route::get('ajax/emails/template/{id?}', 'AjaxController@get_emailtemplate_info');