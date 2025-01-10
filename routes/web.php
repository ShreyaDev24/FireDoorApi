<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\Auth\GoogleSocialiteController;
use App\Http\Controllers\Auth\FacebookSocialiteController;

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

Route::get('/clear-cache', function() {

	Artisan::call('config:clear');
	Artisan::call('cache:clear');
	Artisan::call('config:cache');
	
	echo "config cleared";

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('login', [GoogleSocialiteController::class, 'login'])->name('login');


Route::get('auth/google', [GoogleSocialiteController::class, 'redirectToGoogle']);
Route::get('callback/google', [GoogleSocialiteController::class, 'handleCallback']);

Route::get('auth/facebook', [FacebookSocialiteController::class, 'redirectToFacebook']);
Route::get('callback/facebook', [FacebookSocialiteController::class, 'handleFacebookCallback']);


Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');
