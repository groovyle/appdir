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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/apps', 'AppController@index')->name('apps');
Route::get('/apps/{slug}', 'AppController@page')->name('apps.page');
Route::get('/apps/{slug}/preview', 'AppController@preview')->name('apps.preview');

Route::redirect('/admin', URL::to('/admin/home'));
Route::prefix('admin')->namespace('Admin')->name('admin.')->group(function() {
    Route::get('home', 'HomeController@index')->name('home');

    Route::get('apps', 'AppController@index')->name('apps');
    Route::resource('apps', 'AppController');
    Route::get('apps/{app}/verifications', 'AppController@verifications')->name('apps.verifications');
    Route::get('apps/{app}/changes', 'AppController@changes')->name('apps.changes');
    Route::get('apps/{app}/changes/visuals/{version?}', 'AppController@snippetVisualsComparison')->name('apps.changes.visuals');
    Route::get('apps/{app}/changes/details/{version?}', 'AppController@snippetVersionDetail')->name('apps.changes.details');

    Route::get('apps/{app}/visuals', 'AppController@visuals')->name('apps.visuals');
    Route::post('apps/{app}/visuals', 'AppController@updateVisuals')->name('apps.visuals.save');

    Route::get('app_verifications', 'AppVerificationController@index')->name('app_verifications');
    Route::get('app_verifications/index', 'AppVerificationController@index')->name('app_verifications.index');
    Route::get('app_verifications/{app}/review', 'AppVerificationController@review')->name('app_verifications.review');
    Route::post('app_verifications/{app}/verify', 'AppVerificationController@verify')->name('app_verifications.verify');
});

Route::group(['middleware' => config('filepond.middleware', ['web', 'auth'])], function() {
    Route::prefix(config('filepond.server.url', '/filepond'))->group(function() {
        Route::get('restore/{id}', 'FilepondController@restore')->name('filepond-restore');
    });
});
