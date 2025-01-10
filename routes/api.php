<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordResetRequestController;

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

Route::middleware('auth:api')->post('/refresh', function () {
    return response()->json(['token' => JWTAuth::refresh()]);
});




Route::post('login', [AuthController::class, 'authenticate']);
Route::post('register', [AuthController::class, 'register']);
Route::post('forgot-password', [PasswordResetRequestController::class, 'sendPasswordResetEmail'])->name('passwords.sent');
Route::post('reset', [AuthController::class, 'sendResetResponse'])->name('passwords.reset');
Route::post('otpVerify', [AuthController::class, 'otpVerify'])->name('passwords.otpVerify');

Route::get('callback/google', [AuthController::class, 'handleGoogleCallback']);


Route::group(['middleware' => ['jwt.verify']], function() {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('get-loggedin-user', [AuthController::class, 'getUser']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    Route::post('change-password', [AuthController::class, 'resetPass']);

    Route::post('updateprofile', [AuthController::class, 'updateProfile']);

    Route::get('list-survey', [SurveylistController::class, 'surveyList']);

    Route::get('survey-detail/{id}', [SurveylistController::class, 'surveyDetail']);

    Route::get('company-detail', [SurveylistController::class, 'companyDetails']);

    Route::get('door-detail', [SurveylistController::class, 'doorDetail']);

    Route::get('current-task', [SurveylistController::class, 'currentTask']);
    Route::get('completed-task', [SurveylistController::class, 'completedTask']);
    Route::patch('task-complete-mark/{id}', [SurveylistController::class, 'completedTaskMark']);

    Route::get('start-survey', [SurveylistController::class, 'startSurvey']);

    Route::get('start-survey-all', [SurveylistController::class, 'startSurveyAll']);

    Route::get('confrim-door', [SurveylistController::class, 'confrimDoor']);

    Route::post('door-edit', [SurveylistController::class, 'editDoor']);

    Route::get('door-list', [SurveylistController::class, 'doorList']);

    Route::post('sign-off', [SurveylistController::class, 'signoff']);

    Route::post('update-note', [SurveylistController::class, 'updateNote']);

    Route::get('get-notification', [SurveylistController::class, 'getNotifications']);

    Route::get('unread-notification', [SurveylistController::class, 'unreadNotifications']);

    Route::get('today-notification', [SurveylistController::class, 'todayNotifications']);

    Route::patch('mark-read-notification/{id}', [SurveylistController::class, 'markRead']);
});
