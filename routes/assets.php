<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;

/**
 * Asset Management Routes
 * 
 * All routes are protected by authentication and authorization policies.
 * Assets are READ-ONLY except for specific controlled actions:
 * - Transfer Location
 * - Borrow Asset
 * - Send to Maintenance
 */

Route::middleware(['auth:sanctum'])->prefix('assets')->name('assets.')->group(function () {
    
    // Summary page (overview with metrics)
    Route::get('/summary', [AssetController::class, 'summary'])->name('summary');

    // Asset list (paginated, searchable, filterable)
    Route::get('/', [AssetController::class, 'index'])->name('index');

    // Asset detail page
    Route::get('/{asset}', [AssetController::class, 'show'])->name('show');

    // Controlled Actions (POST requests - state transitions)
    
    /**
     * Transfer Location Action
     * 
     * Preconditions:
     * - Asset status must be 'aktif'
     * - Asset must be available (is_available = true)
     * - Must have permission to update asset
     * 
     * Effects:
     * - Creates AssetTransaction record
     * - Updates asset.location_id
     * - Logs change with created_by and timestamp
     */
    Route::post('/{asset}/transfer-location', [AssetController::class, 'transferLocation'])
        ->name('transfer-location');

    /**
     * Borrow Asset Action
     * 
     * Preconditions:
     * - Asset status must be 'aktif'
     * - Asset must be available (is_available = true)
     * - Condition must be 'baik' or 'perlu_perbaikan'
     * - No active loans exist
     * - Must have permission to update asset
     * 
     * Effects:
     * - Creates AssetLoan record
     * - Updates asset.status = 'dipinjam'
     * - Updates asset.is_available = false
     * - Employee assigned as borrower
     */
    Route::post('/{asset}/borrow', [AssetController::class, 'borrow'])
        ->name('borrow');

    /**
     * Return Borrowed Asset Action
     * 
     * Preconditions:
     * - Asset status must be 'dipinjam'
     * - Active loan must exist
     * - Must have permission to update asset
     * 
     * Effects:
     * - Updates AssetLoan status = 'dikembalikan'
     * - Sets return_date = today
     * - Updates asset.status = 'aktif'
     * - Updates asset.is_available = true
     */
    Route::post('/{asset}/return', [AssetController::class, 'returnAsset'])
        ->name('return');

    /**
     * Send to Maintenance Action
     * 
     * Preconditions:
     * - Asset status must be 'aktif'
     * - Asset must be available (is_available = true)
     * - No active maintenance exists
     * - Must have permission to update asset
     * 
     * Effects:
     * - Creates AssetMaintenance record
     * - Updates asset.status = 'dipelihara'
     * - Updates asset.is_available = false
     */
    Route::post('/{asset}/send-maintenance', [AssetController::class, 'sendMaintenance'])
        ->name('send-maintenance');

    /**
     * Complete Maintenance Action
     * 
     * Preconditions:
     * - Asset status must be 'dipelihara'
     * - Active maintenance must exist
     * - Must have permission to update asset
     * 
     * Effects:
     * - Updates AssetMaintenance status = 'selesai'
     * - Sets completed_date = today
     * - Updates asset.status = 'aktif'
     * - Updates asset.is_available = true
     */
    Route::post('/{asset}/complete-maintenance', [AssetController::class, 'completeMaintenance'])
        ->name('complete-maintenance');
});
