<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssetMaintenanceService
{
    /**
     * Send asset to maintenance
     */
    public function sendToMaintenance(
        Asset $asset,
        string $reason,
        ?string $estimatedCompletionDate = null
    ): AssetMaintenance {
        // Check if asset can be sent to maintenance
        if (!app(AssetService::class)->canPerformAction($asset, 'send_maintenance')) {
            throw new \InvalidArgumentException('Asset cannot be sent to maintenance in its current state');
        }

        // Check if there's already an active maintenance record
        if ($this->hasActiveMaintenance($asset)) {
            throw new \InvalidArgumentException('Asset is already under maintenance');
        }

        // Validate estimated completion date
        if ($estimatedCompletionDate) {
            $completionDate = Carbon::parse($estimatedCompletionDate);
            if ($completionDate->isPast()) {
                throw new \InvalidArgumentException('Estimated completion date cannot be in the past');
            }
        }

        // Create maintenance record
        $maintenance = AssetMaintenance::create([
            'asset_id' => $asset->id,
            'maintenance_date' => now()->toDateString(),
            'estimated_completion_date' => $estimatedCompletionDate,
            'description' => $reason,
            'status' => 'dalam_proses',
            'created_by' => Auth::id(),
        ]);

        // Update asset status
        $asset->update([
            'status' => 'dipelihara',
            'is_available' => false,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $maintenance;
    }

    /**
     * Complete maintenance on an asset
     */
    public function completeMaintenance(int $maintenanceId): AssetMaintenance
    {
        $maintenance = AssetMaintenance::findOrFail($maintenanceId);

        // Validate maintenance is still active
        if ($maintenance->status !== 'dalam_proses') {
            throw new \InvalidArgumentException('Maintenance is not active');
        }

        // Update maintenance record
        $maintenance->update([
            'completed_date' => now()->toDateString(),
            'status' => 'selesai',
        ]);

        // Update asset status
        $asset = $maintenance->asset;
        $asset->update([
            'status' => 'aktif',
            'is_available' => true,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $maintenance;
    }

    /**
     * Cancel maintenance (without completing)
     */
    public function cancelMaintenance(int $maintenanceId): AssetMaintenance
    {
        $maintenance = AssetMaintenance::findOrFail($maintenanceId);

        // Only active maintenance can be canceled
        if ($maintenance->status !== 'dalam_proses') {
            throw new \InvalidArgumentException('Only active maintenance can be canceled');
        }

        // Update maintenance
        $maintenance->update([
            'status' => 'dibatalkan',
        ]);

        // Restore asset to active state
        $asset = $maintenance->asset;
        $asset->update([
            'status' => 'aktif',
            'is_available' => true,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $maintenance;
    }

    /**
     * Get current maintenance record for an asset
     */
    public function getCurrentMaintenance(Asset $asset): ?AssetMaintenance
    {
        return AssetMaintenance::where('asset_id', $asset->id)
            ->whereNull('completed_date')
            ->with('creator')
            ->first();
    }

    /**
     * Check if asset is currently under maintenance
     */
    public function hasActiveMaintenance(Asset $asset): bool
    {
        return AssetMaintenance::where('asset_id', $asset->id)
            ->whereNull('completed_date')
            ->exists();
    }

    /**
     * Get maintenance history
     */
    public function getMaintenanceHistory(Asset $asset)
    {
        return AssetMaintenance::where('asset_id', $asset->id)
            ->with('creator')
            ->orderBy('maintenance_date', 'desc')
            ->paginate(10);
    }

    /**
     * Get overdue maintenance (past estimated completion date)
     */
    public function getOverdueMaintenance()
    {
        return AssetMaintenance::where('status', 'dalam_proses')
            ->where('estimated_completion_date', '<', now()->toDateString())
            ->with(['asset', 'creator'])
            ->orderBy('estimated_completion_date', 'asc')
            ->get();
    }

    /**
     * Get maintenance summary
     */
    public function getMaintenanceSummary(): array
    {
        return [
            'in_progress' => AssetMaintenance::where('status', 'dalam_proses')->count(),
            'completed' => AssetMaintenance::where('status', 'selesai')->count(),
            'cancelled' => AssetMaintenance::where('status', 'dibatalkan')->count(),
            'overdue' => AssetMaintenance::where('status', 'dalam_proses')
                ->where('estimated_completion_date', '<', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * Get average maintenance duration
     */
    public function getAverageMaintenanceDuration(): ?string
    {
        $result = AssetMaintenance::where('status', 'selesai')
            ->selectRaw('AVG(DATEDIFF(completed_date, maintenance_date)) as avg_days')
            ->first();

        return $result->avg_days ? round($result->avg_days, 2) . ' days' : null;
    }
}
