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
use App\Http\Controllers\AnnouncementController;
use App\Http\Middleware\NoCacheForLivewireTmp;

// ============================================================================
// PUBLIC ROUTES (No authentication required)
// ============================================================================

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('feed.index');
    }
    return view('welcome');
})->name('welcome');

// About page - redirect to feed if authenticated
Route::get('/about', function () {
    if (auth()->check()) {
        return redirect()->route('feed.index');
    }
    return view('about');
})->name('about');

// Rewards info page - redirect to feed if authenticated
Route::get('/rewards-info', function () {
    if (auth()->check()) {
        return redirect()->route('feed.index');
    }
    return view('rewards-info');
})->name('rewards-info');

// Authentication Routes
Route::get('/login', [SessionController::class, 'create'])
    ->name('login')
    ->middleware('guest')
    ->defaults('redirectTo', '/feed'); // Redirect to feed if already logged in

Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->name('register')
    ->middleware('guest')
    ->defaults('redirectTo', '/feed'); // Redirect to feed if already logged in

// Public voucher verification route
Route::get('/voucher/verify/{reference_no}', [VoucherController::class, 'verify'])
    ->name('voucher.verify');

// Survey answering routes (require authentication)
Route::middleware('auth')->group(function () {
    Route::get('/surveys/answer/{survey:uuid}', [SurveyController::class, 'answer'])->name('surveys.answer')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::post('/surveys/answer/{survey:uuid}', [SurveyController::class, 'submit'])->name('surveys.submit');
});

// Google OAuth Signup
Route::get('/auth/google/redirect', [\App\Http\Controllers\GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'callback'])->name('google.callback');
Route::post('/auth/google/consent', [\App\Http\Controllers\GoogleAuthController::class, 'consent'])->name('google.consent');

// Privacy Policy and Terms of Use pages
Route::get('/policies/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/policies/terms-of-use', function () {
    return view('terms-of-use');
})->name('terms-of-use');

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
    Route::get('/inbox/{message:uuid}', [InboxController::class, 'show'])->name('inbox.show')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::post('/inbox/test', [InboxController::class, 'sendTestMessage'])->name('inbox.test');

    // Survey management routes (for researchers)
    Route::get('/surveys/{survey:uuid}/responses/{response:uuid}/view', [SurveyController::class, 'showOwnResponse'])->name('surveys.responses.view');
});

// ============================================================================
// ROLE-SPECIFIC ROUTES
// ============================================================================

// Institution Admin Routes
Route::middleware(['auth', 'role:institution_admin'])->group(function () {
    Route::get('/institution/analytics', [InstitutionAdminController::class, 'analyticsIndex'])->name('institution.analytics');
    Route::get('/institution/users', [InstitutionAdminController::class, 'usersIndex'])->name('institution.users');
    Route::get('/institution/surveys', [InstitutionAdminController::class, 'surveysIndex'])->name('institution.surveys');
    
    // Institution admin announcement management
    Route::get('/institution/announcements', [InstitutionAdminController::class, 'announcementIndex'])->name('institution.announcements');
});

// Super Admin Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/analytics', [SuperAdminController::class, 'analyticsIndex'])->name('analytics.index');
    Route::get('/reward-redemptions', [SuperAdminController::class, 'rewardIndex'])->name('reward-redemptions.index');
    Route::get('/users', [SuperAdminController::class, 'userIndex'])->name('users.index');
    Route::get('/surveys', [SuperAdminController::class, 'surveysIndex'])->name('surveys.index');
    Route::get('/requests', [SuperAdminController::class, 'supportRequestsIndex'])->name('support-requests.index');
    
    // Super admin announcement management
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    
    // User management routes
    Route::get('/users/{user:uuid}/profile', [SuperAdminController::class, 'userProfile'])->name('users.profile')->withTrashed();
    Route::put('/users/{user:uuid}/toggle-status', [SuperAdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::delete('/users/{user:uuid}/archive', [SuperAdminController::class, 'archiveUser'])->name('users.archive');
    Route::put('/users/{user:uuid}/restore', [SuperAdminController::class, 'restoreUser'])->name('users.restore')->withTrashed();
});

// Researcher and Institution Admin Routes (can also be accessed by super_admin)
Route::middleware(['auth'])->group(function () {
    // Survey creation and management routes
    Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create.redirect');
    Route::get('/surveys/create/{survey:uuid}', [SurveyController::class, 'create'])->name('surveys.create')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/preview', [SurveyController::class, 'showAnswerFormRedirect'])->name('surveys.preview.redirect');
    Route::get('/surveys/{survey:uuid}/preview', [SurveyController::class, 'showAnswerForm'])->name('surveys.preview')->defaults('isPreview', true)->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/{survey:uuid}/responses', [SurveyController::class, 'showResponses'])->name('surveys.responses')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
    Route::get('/surveys/{survey:uuid}/responses/individual', [SurveyController::class, 'showIndividualResponses'])->name('surveys.responses.individual')->missing(function () {
        abort(404, 'The requested page could not be found.');
    });
});

// Respondent Routes (can also be accessed by other roles)
Route::middleware(['auth', 'role:respondent,researcher,institution_admin,super_admin'])->group(function () {
    // Add respondent-specific routes here
    // Most authenticated routes are available to all user types
});

// If you serve storage via Laravel (not via Nginx directly)
Route::middleware([NoCacheForLivewireTmp::class])->group(function () {
    Route::get('storage/livewire-tmp/{file}', function ($file) {
        // ...your file serving logic...
    });
    // Optionally, add other routes that serve temp files
});

// Remove auth middleware from Livewire temp file route (for debugging only)
Route::get('/livewire/preview-file/{filename}', function ($filename) {
    // ...your logic to serve the file...
})->withoutMiddleware(['auth']);

// ============================================================================
// CATCH-ALL ROUTE (Handle undefined routes with user-friendly message)
// ============================================================================
Route::fallback(function () {
    abort(404, 'The requested page could not be found.');
});