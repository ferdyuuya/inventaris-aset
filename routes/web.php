<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('/employees', 'employees')
    ->middleware(['auth', 'verified'])
    ->name('employees');

Route::view('/users', 'users')
    ->middleware(['auth', 'verified'])
    ->name('users');

require __DIR__.'/settings.php';
