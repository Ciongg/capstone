<?php

use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Surveys\FormBuilder;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/surveys/create', [SurveyController::class, 'create'])->name('surveys.create');





Route::get('/dashboard', [SessionController::class, 'showDashboard'])->name('dashboard');
Route::get('/login', [SessionController::class, 'showLogin'])->name('show.login');
Route::post('/login', [SessionController::class, 'store'])->name('login');
Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');