<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;

class AssetLocationService
{
    /**
     * Transfer asset to a new location
     */
    public function transferAsset(Asset $asset, int $toLocationId, string $reason): AssetTransaction
    {
        // Validate the new location exists
        $toLocation = Location::findOrFail($toLocationId);

        // Prevent transferring to the same location
        if ($asset->location_id === $toLocationId) {
            throw new \InvalidArgumentException('Asset is already at this location');
        }

        // Validate asset can be transferred
        if (!app(AssetService::class)->canPerformAction($asset, 'transfer_location')) {
            throw new \InvalidArgumentException('Asset cannot be transferred in its current state');
        }

        // Record the transaction
        $transaction = AssetTransaction::create([
            'asset_id' => $asset->id,
            'type' => 'mutasi',
            'from_location_id' => $asset->location_id,
            'to_location_id' => $toLocationId,
            'transaction_date' => now()->toDateString(),
            'description' => $reason,
            'created_by' => Auth::id(),
        ]);

        // Update asset location
        $asset->update(['location_id' => $toLocationId]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $transaction;
    }

    /**
     * Get location history for an asset
     */
    public function getLocationHistory(Asset $asset)
    {
        return AssetTransaction::where('asset_id', $asset->id)
            ->where('type', 'mutasi')
            ->with(['fromLocation', 'toLocation', 'creator'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get current location with metadata
     */
    public function getCurrentLocation(Asset $asset): array
    {
        $location = $asset->location;

        return [
            'id' => $location->id,
            'name' => $location->name,
            'description' => $location->description,
            'responsible_employee_id' => $location->responsible_employee_id,
            'responsible_employee' => $location->responsibleEmployee,
        ];
    }

    /**
     * Get location transfer timeline
     */
    public function getTransferTimeline(Asset $asset)
    {
        return AssetTransaction::where('asset_id', $asset->id)
            ->where('type', 'mutasi')
            ->select([
                'id',
                'from_location_id',
                'to_location_id',
                'transaction_date',
                'description',
            ])
            ->with(['fromLocation:id,name', 'toLocation:id,name'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);
    }
}
