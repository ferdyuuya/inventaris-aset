<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestAssetGenerationController;
use App\Http\Controllers\AssetController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Test route for asset generation
// Route::get('/test-asset-generation', [TestAssetGenerationController::class, 'test'])
//     ->middleware(['auth', 'verified'])
//     ->name('test-asset-generation');

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

// ============================================================
// ASSET MANAGEMENT ROUTES (Read-Only with Controlled Actions)
// ============================================================
// Assets are generated from procurement records.
// Users cannot manually create, edit, or delete assets.
// Only controlled lifecycle actions are allowed:
// - Transfer Location
// - Borrow Asset
// - Return Asset
// - Send to Maintenance
// - Complete Maintenance
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('assets')->name('assets.')->group(function () {
    // Summary page (overview with metrics)
    Route::get('/summary', [AssetController::class, 'summary'])->name('summary');

    // Asset list (paginated, searchable, filterable)
    Route::get('/', [AssetController::class, 'index'])->name('index');

    // Asset detail page
    Route::get('/{asset}', [AssetController::class, 'show'])->name('show');

    // Controlled Actions (POST requests - state transitions)
    Route::post('/{asset}/transfer-location', [AssetController::class, 'transferLocation'])->name('transfer-location');
    Route::post('/{asset}/borrow', [AssetController::class, 'borrow'])->name('borrow');
    Route::post('/{asset}/return', [AssetController::class, 'returnAsset'])->name('return');
    Route::post('/{asset}/send-maintenance', [AssetController::class, 'sendMaintenance'])->name('send-maintenance');
    Route::post('/{asset}/complete-maintenance', [AssetController::class, 'completeMaintenance'])->name('complete-maintenance');
});

require __DIR__.'/settings.php';
