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

Route::view('/asset-categories', 'asset-categories')
    ->middleware(['auth', 'verified'])
    ->name('asset-categories');

Route::view('/suppliers', 'suppliers')
    ->middleware(['auth', 'verified'])
    ->name('suppliers');

Route::view('/procurements', 'procurements')
    ->middleware(['auth', 'verified'])
    ->name('procurements');

Route::view('/locations', 'locations')
    ->middleware(['auth', 'verified'])
    ->name('locations');

require __DIR__.'/settings.php';
