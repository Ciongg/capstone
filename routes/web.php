<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Surveys\FormBuilder;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/surveys/create', FormBuilder::class)->name('surveys.create');
