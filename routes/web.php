<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestAssetGenerationController;
use App\Http\Controllers\AssetController;

// ============================================================
// ROOT ROUTE - Redirect to Dashboard
// ============================================================
Route::redirect('/', '/dashboard');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ============================================================
// MASTER DATA ROUTES (ADMIN ONLY - MUTATING)
// ============================================================

Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::view('/employees', 'pages.masterdata.employee.index')->name('employees');
    Route::view('/asset-categories', 'pages.masterdata.category.index')->name('asset-categories');
    Route::view('/suppliers', 'pages.masterdata.supplier.index')->name('suppliers');
    Route::view('/locations', 'pages.masterdata.location.index')->name('locations');
});

// ============================================================
// USER MANAGEMENT ROUTES (ADMIN ONLY)
// ============================================================

Route::view('/users', 'pages.users.index')
    ->middleware(['auth', 'verified', 'admin'])
    ->name('users');

// ============================================================
// PROCUREMENT ROUTES
// ============================================================

// Procurement list (viewable by all authenticated users)
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
    // Summary page (overview with metrics) - READ ONLY
    Route::get('/summary', [AssetController::class, 'summary'])->name('summary');

    // Asset list (via controller, displays Livewire component) - READ ONLY
    Route::get('/', [AssetController::class, 'index'])->name('index');

    // Asset detail page - READ ONLY
    Route::get('/{asset}', [AssetController::class, 'show'])->name('show');

    // Controlled Actions (POST requests - state transitions) - ADMIN ONLY
    Route::middleware(['admin'])->group(function () {
        Route::post('/{asset}/transfer-location', [AssetController::class, 'transferLocation'])->name('transfer-location');
        Route::post('/{asset}/borrow', [AssetController::class, 'borrow'])->name('borrow');
        Route::post('/{asset}/return', [AssetController::class, 'returnAsset'])->name('return');
        Route::post('/{asset}/send-maintenance', [AssetController::class, 'sendMaintenance'])->name('send-maintenance');
        Route::post('/{asset}/complete-maintenance', [AssetController::class, 'completeMaintenance'])->name('complete-maintenance');
    });
});

// ============================================================
// ASSET LOAN MANAGEMENT ROUTES
// Admin-controlled borrowing workflow
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('asset-loans')->name('asset-loans.')->group(function () {
    // Asset loans list page - READ ONLY (viewable by all)
    Route::get('/', fn () => view('pages.asset-loans.index'))->name('index');
});

// ============================================================
// MAINTENANCE MANAGEMENT ROUTES
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('maintenance')->name('maintenance.')->group(function () {
    // Maintenance requests list - viewable by all, staff can create requests
    Route::get('/requests', fn () => view('pages.maintenance.requests'))->name('requests.index');

    // Asset maintenances list - viewable by all
    Route::get('/assets', fn () => view('pages.maintenance.assets'))->name('assets.index');
});

// ============================================================
// INSPECTION ROUTES
// ============================================================

Route::middleware(['auth', 'verified'])->prefix('inspections')->name('inspections.')->group(function () {
    // Inspection index (list all inspections) - viewable by all
    Route::get('/', fn () => view('pages.inspections.index'))->name('index');
});

require __DIR__.'/settings.php';
