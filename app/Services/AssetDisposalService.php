<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetDisposal;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * AssetDisposalService
 * 
 * Owns all business logic for asset disposal.
 * Handles atomic state transitions and guarantees data consistency.
 * 
 * CORE PRINCIPLES:
 * 1. Disposal is NOT deletion - asset remains in database
 * 2. Disposal is FINAL and IRREVERSIBLE
 * 3. Disposal requires a reason for audit
 * 4. Disposal blocked if asset is in use
 * 
 * All operations are transactional:
 * - If any step fails, entire transaction rolls back
 * - No partial updates or corrupted states
 * - Exceptions thrown on validation or database errors
 */
class AssetDisposalService
{
    /**
     * Dispose an asset permanently
     * 
     * ATOMIC OPERATION:
     * 1. Validate asset can be disposed
     * 2. Update asset: status → 'dihapuskan', is_available → false, disposed_at → now
     * 3. Create asset_disposals audit record
     * 
     * @param Asset $asset The asset to dispose
     * @param User $admin The admin disposing the asset
     * @param string $reason The reason for disposal (required)
     * 
     * @return AssetDisposal The created disposal audit record
     * 
     * @throws Exception If asset cannot be disposed or any operation fails
     */
    public function dispose(Asset $asset, User $admin, string $reason): AssetDisposal
    {
        // VALIDATION 1: Reason is required
        if (empty(trim($reason))) {
            throw new Exception('Disposal reason is required.');
        }

        // VALIDATION 2: Asset is not already disposed
        if ($asset->isDisposed()) {
            throw new Exception(
                "Cannot dispose asset. Asset '{$asset->asset_code}' is already disposed."
            );
        }

        // VALIDATION 3: Asset is not currently borrowed
        if ($asset->isBorrowed()) {
            throw new Exception(
                "Cannot dispose asset. Asset '{$asset->asset_code}' is currently borrowed. " .
                "Please return the asset first."
            );
        }

        // VALIDATION 4: Asset is not under active maintenance
        if ($asset->isUnderMaintenance()) {
            throw new Exception(
                "Cannot dispose asset. Asset '{$asset->asset_code}' is under active maintenance. " .
                "Please complete or cancel the maintenance first."
            );
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($asset, $admin, $reason): AssetDisposal {
                $now = now();

                // STEP 1: Update asset state to disposed
                $asset->update([
                    'status' => 'dihapuskan',
                    'is_available' => false,
                    'disposed_at' => $now,
                ]);

                // STEP 2: Create disposal audit record
                $disposal = AssetDisposal::create([
                    'asset_id' => $asset->id,
                    'disposed_by' => $admin->id,
                    'reason' => trim($reason),
                    'disposed_at' => $now,
                ]);

                return $disposal;
            });
        } catch (Exception $e) {
            // Re-throw with context for Livewire to handle
            throw new Exception("Failed to dispose asset: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Check if an asset can be disposed
     * 
     * Returns array with 'can_dispose' boolean and 'reason' string
     * 
     * @param Asset $asset The asset to check
     * @return array{can_dispose: bool, reason: string|null}
     */
    public function canDispose(Asset $asset): array
    {
        if ($asset->isDisposed()) {
            return [
                'can_dispose' => false,
                'reason' => 'Asset is already disposed.',
            ];
        }

        if ($asset->isBorrowed()) {
            return [
                'can_dispose' => false,
                'reason' => 'Asset is currently borrowed. Please return it first.',
            ];
        }

        if ($asset->isUnderMaintenance()) {
            return [
                'can_dispose' => false,
                'reason' => 'Asset is under active maintenance. Complete or cancel maintenance first.',
            ];
        }

        return [
            'can_dispose' => true,
            'reason' => null,
        ];
    }

    /**
     * Get disposal history for an asset
     * 
     * @param Asset $asset
     * @return AssetDisposal|null
     */
    public function getDisposalRecord(Asset $asset): ?AssetDisposal
    {
        return $asset->disposal()->with('disposedBy')->first();
    }
}
