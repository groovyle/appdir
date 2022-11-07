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

Auth::routes([
	// See Illuminate\Routing\Router::auth()
	'reset'	=> false,
]);
Route::get('/register/done', 'Auth\\AfterRegisterController@afterRegister')->name('after_register');

Route::get('/', 'HomeController@index')->name('index');
// Route::get('/home', 'HomeController@home')->name('home');
Route::redirect('/home', URL::to('/'))->name('home');
Route::get('/apps', 'AppController@index')->name('apps');
Route::get('/apps/{slug}', 'AppController@page')->name('apps.page');
Route::get('/apps/{slug}/preview', 'AppController@preview')->name('apps.preview');
Route::post('/apps/{slug}/submit_report', 'AppController@postReport')->name('apps.report.save');
Route::get('/user/{user}', 'UserController@profile')->name('user.profile');
Route::get('/login/error', 'Auth\\LoginController@errorPage')->name('login_error');
Route::patch('/change_language', 'UserController@changeLanguage')->name('change_language');

Route::get('/stats/apps', 'StatisticsController@apps')->name('stats.apps');

Route::get('/color_test', 'TestColorsController@index')->name('color_test');

Route::redirect('/admin', URL::to('/admin/home'))->name('admin');
Route::prefix('admin')->namespace('Admin')->name('admin.')->group(function() {
	Route::get('home', 'DashboardController@index')->name('home');

	Route::get('profile', 'UserProfileController@show')->name('profile.index');
	Route::get('profile/edit', 'UserProfileController@editProfile')->name('profile.edit');
	Route::patch('profile/edit', 'UserProfileController@updateProfile')->name('profile.edit.save');
	Route::get('profile/picture', 'UserProfileController@editPicture')->name('profile.picture');
	Route::post('profile/picture', 'UserProfileController@updatePicture')->name('profile.picture.save');
	Route::get('profile/password', 'UserProfileController@editPassword')->name('profile.password');
	Route::patch('profile/password', 'UserProfileController@updatePassword')->name('profile.password.save');

	Route::get('apps', 'AppController@index')->name('apps');
	Route::get('apps/activities', 'AppController@activityLog')->name('app_activities.index');

	Route::resource('apps', 'AppController');
	Route::get('apps/{app}/verifications', 'AppController@verifications')->name('apps.verifications');
	Route::get('apps/{app}/changes', 'AppController@changes')->name('apps.changes');
	Route::get('apps/{app}/switch/{version}', 'AppController@switchVersionForm')->name('apps.switch_version');
	Route::post('apps/{app}/switch/{version}', 'AppController@switchToVersion')->name('apps.switch_version.save');
	Route::get('apps/{app}/visuals', 'AppController@visuals')->name('apps.visuals');
	Route::post('apps/{app}/visuals', 'AppController@updateVisuals')->name('apps.visuals.save');
	Route::get('apps/{app}/publish', 'AppController@reviewChanges')->name('apps.publish');
	Route::post('apps/{app}/publish', 'AppController@publishChanges')->name('apps.publish.save');
	Route::get('apps/{app}/published', 'AppController@afterPublishChanges')->name('apps.published');

	Route::post('apps/{app}/set_private/{private?}', 'AppController@setPrivate')->name('apps.set-private');
	Route::post('apps/{app}/set_published/{published?}', 'AppController@setPublished')->name('apps.set-published');

	// The route is reversed for ajax stuffs so that URL management is easy
	// (i.e put all parameters at the end).
	// TODO: put these AJAX calls outside of login middleware
	Route::get('apps/changes/visuals/{app?}/{version?}', 'AppController@snippetVisualsComparison')->name('apps.changes.visuals');
	Route::get('apps/changes/details/{app?}/{version?}', 'AppController@snippetVersionDetail')->name('apps.changes.details');
	Route::get('apps/changes/pending/{app?}/{version?}', 'AppController@snippetPendingChanges')->name('apps.changes.pending');
	Route::get('apps/changes/pending_versions/{app?}', 'AppController@jsonPendingVersions')->name('apps.changes.pending_versions');

	Route::get('app_verifications', 'AppVerificationController@index')->name('app_verifications.index');
	Route::get('app_verifications/{app}/review/{verif?}', 'AppVerificationController@review')->name('app_verifications.review');
	Route::post('app_verifications/{app}/verify', 'AppVerificationController@verify')->name('app_verifications.verify');
	Route::get('app_verifications/details/{verif_id?}', 'AppVerificationController@snippetDetail')->name('app_verifications.details_snippet');

	Route::get('app_verifications/{app}/advanced_review', 'AppVerificationController@advancedReview')->name('app_verifications.advanced_review');
	Route::post('app_verifications/{app}/advanced_verify', 'AppVerificationController@advancedVerify')->name('app_verifications.advanced_verify');

	Route::get('app_reports', 'AppReportController@index')->name('app_reports.index');
	Route::get('app_reports/{app}/review', 'AppReportController@review')->name('app_reports.review');
	Route::post('app_reports/{app}/verify', 'AppReportController@verify')->name('app_reports.verify');
	Route::get('app_reports/{app}/verdicts', 'AppReportController@verdicts')->name('app_reports.verdicts');

	Route::resource('app_categories', 'AppCategoryController')->parameters([
		'app_categories'	=> 'cat',
	]);

	Route::resource('app_tags', 'AppTagController')->parameters([
		'app_tags'	=> 'tag',
	]);

	Route::resource('prodi', 'ProdiController');

	Route::get('users/lookup/{keyword?}', 'UserController@lookup')->name('users.lookup');
	Route::patch('users/{user}/reset_password', 'UserController@resetPassword')->name('users.reset_password.save');
	Route::get('users/{user}/reset_password', 'UserController@afterResetPassword')->name('users.reset_password');
	Route::get('users/{user}/block', 'UserController@blockForm')->name('users.block');
	Route::post('users/{user}/block', 'UserController@block')->name('users.block.save');
	Route::post('users/{user}/unblock', 'UserController@unblock')->name('users.unblock.save');
	Route::get('users/{user}/block_history', 'UserController@blockHistory')->name('users.block_history');
	Route::resource('users', 'UserController');

	Route::resource('roles', 'RoleController');
	Route::resource('abilities', 'AbilityController')->parameters([
		'abilities'	=> 'abl',
	]);
	Route::resource('settings', 'SettingController')->parameters([
		'settings'	=> 'stt',
	]);
	Route::resource('log_actions', 'LogActionController')->only(['index', 'show'])->parameters([
		'log_actions'	=> 'log',
	]);

	Route::get('stats/app_activities', 'StatisticsController@app_activities')->name('stats.app_activities');
});

Route::group(['middleware' => config('filepond.middleware', ['web', 'auth'])], function() {
	Route::prefix(config('filepond.server.url', '/filepond'))->group(function() {
		Route::get('restore/{id}', 'FilepondController@restore')->name('filepond-restore');
	});
});
