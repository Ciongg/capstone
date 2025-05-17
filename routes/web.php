<?php

use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SessionController; // Keep for logout and now for login view
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisteredUserController; // Ensure this is imported
use App\Http\Controllers\RewardController; // Ensure this is imported
use App\Http\Controllers\SuperAdminController; // Ensure this is imported
use App\Http\Controllers\InstitutionAdminController; // Ensure this is imported

Route::get('/', function () {
    return view('welcome');
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
    // Route::get('/my-surveys', [SurveyController::class, 'showSurveys'])->name('my-surveys.index');
    
    Route::get('/surveys/{survey}/preview', [SurveyController::class, 'showAnswerForm'])->name('surveys.preview')->defaults('isPreview', true);
    Route::get('/institution/analytics', [InstitutionAdminController::class, 'analyticsIndex'])->name('institution.analytics');
});

Route::get('/surveys/answer/{survey}', [SurveyController::class, 'showAnswerForm'])->name('surveys.answer');
Route::post('/surveys/answer/{survey}', [SurveyController::class, 'submit'])->name('surveys.submit');

Route::get('/surveys/{survey}/responses', [SurveyController::class, 'showResponses'])->name('surveys.responses');
Route::get('/surveys/{survey}/responses/individual', [SurveyController::class, 'showIndividualResponses'])->name('surveys.responses.individual');


Route::get('/admin/reward-redemptions', [SuperAdminController::class, 'rewardIndex'])->name('reward-redemptions.index');
Route::get('/admin/users', [SuperAdminController::class, 'userIndex'])->name('users.index');
Route::get('/admin/surveys', [SuperAdminController::class, 'surveysIndex'])->name('surveys.index');
Route::get('/admin/users/{user}/profile', [SuperAdminController::class, 'userProfile'])->name('users.profile')->withTrashed();
Route::put('/admin/users/{user}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
Route::delete('/admin/users/{user}/archive', [SuperAdminController::class, 'archiveUser'])->name('users.archive');
Route::put('/admin/users/{user}/restore', [SuperAdminController::class, 'restoreUser'])->name('users.restore')->withTrashed();