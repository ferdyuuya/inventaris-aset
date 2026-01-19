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

// ============================================================
// MASTER DATA ROUTES
// ============================================================

Route::view('/employees', 'pages.masterdata.employee.index')
    ->middleware(['auth', 'verified'])
    ->name('employees');

Route::view('/asset-categories', 'pages.masterdata.category.index')
    ->middleware(['auth', 'verified'])
    ->name('asset-categories');

Route::view('/suppliers', 'pages.masterdata.supplier.index')
    ->middleware(['auth', 'verified'])
    ->name('suppliers');

Route::view('/locations', 'pages.masterdata.location.index')
    ->middleware(['auth', 'verified'])
    ->name('locations');

// ============================================================
// USER MANAGEMENT ROUTES
// ============================================================

Route::view('/users', 'pages.users.index')
    ->middleware(['auth', 'verified'])
    ->name('users');

// ============================================================
// PROCUREMENT ROUTES
// ============================================================

Route::view('/procurements', 'pages.procurements.index')
    ->middleware(['auth', 'verified'])
    ->name('procurements');

Route::get('/procurements/{id}', function ($id) {
    $procurement = \App\Models\Procurement::with(['category', 'supplier', 'location'])->findOrFail($id);
    return view('pages.procurements.detail', compact('id', 'procurement'));
})
    ->name('procurements.detail')
    ->middleware(['auth', 'verified']);

// ============================================================
// ASSET MANAGEMENT ROUTES
// Assets are generated from procurement records.
// Users cannot manually create, edit, or delete assets.
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('assets')->name('assets.')->group(function () {
    // Summary page (overview with metrics)
    Route::get('/summary', [AssetController::class, 'summary'])->name('summary');

    // Asset list (via controller, displays Livewire component)
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
