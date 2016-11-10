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


// UserController Routes...
Route::get('profile/{id?}', 'UserController@get_editprofile');
Route::post('profile', 'UserController@post_editprofile');


// TargetsController Routes...
Route::get('targets', 'TargetsController@index');
Route::post('targets/add', 'TargetsController@addTarget');
Route::post('targets/import', 'TargetsController@importTargets');
Route::get('targets/list', 'TargetsController@targetlists_index');


// Dashboard Routes...
Route::get('/home', 'DashboardController@index');
Route::get('/', 'DashboardController@index');



// Ajax Routes...
Route::post('ajax/targetuser/note', 'AjaxController@edit_targetuser_notes');
