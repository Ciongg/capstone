<?php

use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Surveys\FormBuilder;
use App\Livewire\Surveys\FormResponses\IndividualResponses;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');



Route::get('/surveys/create/{survey?}', [SurveyController::class, 'create'])->name('surveys.create');
Route::get('/my-surveys', [SurveyController::class, 'showSurveys'])->name('my-surveys.index');


Route::get('/surveys/answer/{survey}', [SurveyController::class, 'answer'])->name('surveys.answer');
Route::post('/surveys/answer/{survey}', [SurveyController::class, 'submit'])->name('surveys.submit');

Route::get('/surveys/{survey}/responses', [SurveyController::class, 'showResponses'])->name('surveys.responses');
Route::get('/surveys/{survey}/responses/individual', [SurveyController::class, 'showIndividualResponses'])->name('surveys.responses.individual');

Route::get('/login', [SessionController::class, 'showLogin'])->name('show.login');
Route::post('/login', [SessionController::class, 'store'])->name('login');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');