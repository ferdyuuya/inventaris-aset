<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Illuminate\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class AssetService
{
    /**
     * Get asset summary metrics
     */
    public function getSummaryMetrics(): array
    {
        return cache()->remember('asset.summary', 300, function () {
            return [
                'total_assets' => $this->getTotalAssets(),
                'available_assets' => $this->getAvailableAssets(),
                'under_maintenance' => $this->getUnderMaintenanceAssets(),
                'currently_borrowed' => $this->getCurrentlyBorrowedAssets(),
                'by_category' => $this->getAssetsByCategory(),
                'by_location' => $this->getAssetsByLocation(),
                'by_status' => $this->getAssetsByStatus(),
            ];
        });
    }

    /**
     * Get paginated asset list with filters and search
     */
    public function getAssetList(array $filters = [], int $perPage = 25)
    {
        $query = Asset::query()
            ->with(['category', 'location', 'supplier'])
            ->orderBy('created_at', 'desc');

        // Apply search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Apply filters
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        // Exclude inactive/retired assets by default unless explicitly requested
        if (!isset($filters['include_nonaktif']) || !$filters['include_nonaktif']) {
            $query->where('status', '!=', 'nonaktif');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get detailed asset information
     */
    public function getAssetDetail(int $assetId): ?Asset
    {
        return Asset::with([
            'category',
            'location',
            'supplier',
            'transactions',
            'loans',
            'maintenances',
        ])->findOrFail($assetId);
    }

    /**
     * Get asset transaction history
     */
    public function getAssetHistory(int $assetId): Collection
    {
        return Asset::findOrFail($assetId)
            ->transactions()
            ->with(['creator', 'fromLocation', 'toLocation'])
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    /**
     * Get asset borrowing history
     */
    public function getBorrowingHistory(int $assetId): Collection
    {
        return Asset::findOrFail($assetId)
            ->loans()
            ->with('borrower')
            ->orderBy('loan_date', 'desc')
            ->get();
    }

    /**
     * Get asset maintenance history
     */
    public function getMaintenanceHistory(int $assetId): Collection
    {
        return Asset::findOrFail($assetId)
            ->maintenances()
            ->with('creator')
            ->orderBy('maintenance_date', 'desc')
            ->get();
    }

    /**
     * Check if asset can perform a specific action
     */
    public function canPerformAction(Asset $asset, string $action): bool
    {
        return match ($action) {
            'transfer_location' => $asset->status === 'aktif' && $asset->is_available,
            'borrow' => $asset->status === 'aktif' && $asset->is_available && 
                       in_array($asset->condition, ['baik', 'perlu_perbaikan']),
            'return_borrow' => $asset->status === 'dipinjam',
            'send_maintenance' => $asset->status === 'aktif' && $asset->is_available,
            'complete_maintenance' => $asset->status === 'dipelihara',
            'retire' => $asset->status === 'aktif',
            default => false,
        };
    }

    /**
     * Get available actions for an asset
     */
    public function getAvailableActions(Asset $asset): array
    {
        $actions = [];

        if ($this->canPerformAction($asset, 'transfer_location')) {
            $actions[] = 'transfer_location';
        }

        if ($this->canPerformAction($asset, 'borrow')) {
            $actions[] = 'borrow';
        }

        if ($this->canPerformAction($asset, 'return_borrow')) {
            $actions[] = 'return_borrow';
        }

        if ($this->canPerformAction($asset, 'send_maintenance')) {
            $actions[] = 'send_maintenance';
        }

        if ($this->canPerformAction($asset, 'complete_maintenance')) {
            $actions[] = 'complete_maintenance';
        }

        return $actions;
    }

    /**
     * Invalidate asset summary cache
     */
    public function invalidateSummaryCache(): void
    {
        cache()->forget('asset.summary');
    }

    /**
     * Get total assets count
     */
    private function getTotalAssets(): int
    {
        return Asset::where('status', '!=', 'nonaktif')->count();
    }

    /**
     * Get available assets count
     */
    private function getAvailableAssets(): int
    {
        return Asset::where('status', 'aktif')
            ->where('is_available', true)
            ->count();
    }

    /**
     * Get assets under maintenance count
     */
    private function getUnderMaintenanceAssets(): int
    {
        return Asset::where('status', 'dipelihara')->count();
    }

    /**
     * Get currently borrowed assets count
     */
    private function getCurrentlyBorrowedAssets(): int
    {
        return Asset::where('status', 'dipinjam')->count();
    }

    /**
     * Get assets grouped by category
     */
    private function getAssetsByCategory(): Collection
    {
        return Asset::select('category_id')
            ->where('status', '!=', 'nonaktif')
            ->groupBy('category_id')
            ->selectRaw('category_id, COUNT(*) as total')
            ->with('category')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->category->name ?? 'Unknown' => $item->total
            ]);
    }

    /**
     * Get assets grouped by location
     */
    private function getAssetsByLocation(): Collection
    {
        return Asset::select('location_id')
            ->where('status', '!=', 'nonaktif')
            ->groupBy('location_id')
            ->selectRaw('location_id, COUNT(*) as total')
            ->with('location')
            ->get()
            ->mapWithKeys(fn($item) => [
                $item->location->name ?? 'Unknown' => $item->total
            ]);
    }

    /**
     * Get assets grouped by status
     */
    private function getAssetsByStatus(): array
    {
        $statuses = ['aktif', 'dipinjam', 'dipelihara'];
        $result = [];

        foreach ($statuses as $status) {
            $result[$status] = Asset::where('status', $status)->count();
        }

        return $result;
    }
}
