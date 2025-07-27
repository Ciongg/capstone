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

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================





Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/rewards-info', function () {
    return view('rewards-info');
})->name('rewards-info');

// Authentication Routes
Route::get('/login', [SessionController::class, 'create'])->name('login');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');

// Public voucher verification route
Route::get('/voucher/verify/{reference_no}', [VoucherController::class, 'verify'])
    ->name('voucher.verify');

// Survey answering routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/surveys/answer/{survey}', [SurveyController::class, 'answer'])->name('surveys.answer')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::post('/surveys/answer/{survey}', [SurveyController::class, 'submit'])->name('surveys.submit');
});

// Google OAuth Signup
Route::get('/auth/google/redirect', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('google.callback');

// ============================================================================
// AUTHENTICATED ROUTES (Requires login)
// ============================================================================
Route::middleware('auth')->group(function () {
    // General authenticated user routes
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
    Route::get('/vouchers', [RewardController::class, 'vouchersIndex'])->name('vouchers.index');

    // Inbox Routes
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{message}', [InboxController::class, 'show'])->name('inbox.show')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::post('/inbox/test', [InboxController::class, 'sendTestMessage'])->name('inbox.test');

    // Survey management routes (for researchers)
    Route::get('/surveys/{survey}/responses/{response}/view', [SurveyController::class, 'showOwnResponse'])->name('surveys.responses.view');
});

// ============================================================================
// ROLE-SPECIFIC ROUTES
// ============================================================================

// Institution Admin Routes
Route::middleware(['auth', 'role:institution_admin'])->group(function () {
    Route::get('/institution/analytics', [InstitutionAdminController::class, 'analyticsIndex'])->name('institution.analytics');
    Route::get('/institution/users', [InstitutionAdminController::class, 'usersIndex'])->name('institution.users');
    Route::get('/institution/surveys', [InstitutionAdminController::class, 'surveysIndex'])->name('institution.surveys');
});

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/analytics', [SuperAdminController::class, 'analyticsIndex'])->name('analytics.index');
    Route::get('/reward-redemptions', [SuperAdminController::class, 'rewardIndex'])->name('reward-redemptions.index');
    Route::get('/users', [SuperAdminController::class, 'userIndex'])->name('users.index');
    Route::get('/surveys', [SuperAdminController::class, 'surveysIndex'])->name('surveys.index');
    Route::get('/requests', [SuperAdminController::class, 'supportRequestsIndex'])->name('support-requests.index');
    Route::get('/reports', [SuperAdminController::class, 'reportsIndex'])->name('reports.index');
    
    // User management routes
    Route::get('/users/{user}/profile', [SuperAdminController::class, 'userProfile'])->name('users.profile')->withTrashed();
    Route::put('/users/{user}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}/archive', [SuperAdminController::class, 'archiveUser'])->name('users.archive');
    Route::put('/users/{user}/restore', [SuperAdminController::class, 'restoreUser'])->name('users.restore')->withTrashed();
});

// Researcher and Institution Admin Routes (can also be accessed by super_admin)
Route::middleware(['auth'])->group(function () {
    // Survey creation and management routes
    Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create.redirect');
    Route::get('/surveys/create/{survey}', [SurveyController::class, 'create'])->name('surveys.create')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/preview', [SurveyController::class, 'showAnswerFormRedirect'])->name('surveys.preview.redirect');
    Route::get('/surveys/{survey}/preview', [SurveyController::class, 'showAnswerForm'])->name('surveys.preview')->defaults('isPreview', true)->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/{survey}/responses', [SurveyController::class, 'showResponses'])->name('surveys.responses')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/{survey}/responses/individual', [SurveyController::class, 'showIndividualResponses'])->name('surveys.responses.individual')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
});

// Respondent Routes (can also be accessed by other roles)
Route::middleware(['auth', 'role:respondent,researcher,institution_admin,super_admin'])->group(function () {
    // Add respondent-specific routes here
    // Most authenticated routes are available to all user types
});

// ============================================================================
// CATCH-ALL ROUTE (Handle undefined routes with user-friendly message)
// ============================================================================
Route::fallback(function () {
    abort(404, 'The requested page could not be found.');
});