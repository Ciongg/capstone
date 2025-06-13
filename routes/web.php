<?php

use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SessionController; 
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\RewardController; 
use App\Http\Controllers\SuperAdminController; 
use App\Http\Controllers\InstitutionAdminController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\InboxController; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/about', function () {
    return view('about');
});

Route::get('/rewards-info', function () {
    return view('rewards-info');
});

// Authentication Routes
Route::get('/login', [SessionController::class, 'create'])->name('login'); // Points to controller
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register'); // Points to controller


Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
Route::get('/vouchers', [RewardController::class, 'vouchersIndex'])->name('vouchers.index');

Route::middleware('auth')->group(function () {
    Route::get('/surveys/create/{survey?}', [SurveyController::class, 'create'])->name('surveys.create'); // For opening/editing existing
    Route::get('/surveys/{survey}/preview', [SurveyController::class, 'showAnswerForm'])->name('surveys.preview')->defaults('isPreview', true);
    Route::get('/institution/analytics', [InstitutionAdminController::class, 'analyticsIndex'])->name('institution.analytics');
    Route::get('/institution/users', [InstitutionAdminController::class, 'usersIndex'])->name('institution.users');

    // Inbox Routes
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{message}', [InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/test', [InboxController::class, 'sendTestMessage'])->name('inbox.test');
});

Route::get('/surveys/answer/{survey}', [SurveyController::class, 'showAnswerForm'])->name('surveys.answer');
Route::post('/surveys/answer/{survey}', [SurveyController::class, 'submit'])->name('surveys.submit');

Route::get('/surveys/{survey}/responses', [SurveyController::class, 'showResponses'])->name('surveys.responses');
Route::get('/surveys/{survey}/responses/individual', [SurveyController::class, 'showIndividualResponses'])->name('surveys.responses.individual');
Route::get('/surveys/{survey}/responses/{response}/view', [SurveyController::class, 'showOwnResponse'])->name('surveys.responses.view');


Route::get('/admin/reward-redemptions', [SuperAdminController::class, 'rewardIndex'])->name('reward-redemptions.index');
Route::get('/admin/users', [SuperAdminController::class, 'userIndex'])->name('users.index');
Route::get('/admin/surveys', [SuperAdminController::class, 'surveysIndex'])->name('surveys.index');
Route::get('/admin/requests', [SuperAdminController::class, 'supportRequestsIndex'])->name('support-requests.index'); // New route for support requests
Route::get('/admin/reports', [SuperAdminController::class, 'reportsIndex'])->name('reports.index'); // New route for support requests
Route::get('/admin/users/{user}/profile', [SuperAdminController::class, 'userProfile'])->name('users.profile')->withTrashed();
Route::put('/admin/users/{user}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
Route::delete('/admin/users/{user}/archive', [SuperAdminController::class, 'archiveUser'])->name('users.archive');
Route::put('/admin/users/{user}/restore', [SuperAdminController::class, 'restoreUser'])->name('users.restore')->withTrashed();

// Public voucher verification route - using controller approach
Route::get('/voucher/verify/{reference_no}', [VoucherController::class, 'verify'])
    ->name('voucher.verify');