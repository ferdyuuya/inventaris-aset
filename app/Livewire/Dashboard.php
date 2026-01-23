<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\AssetMaintenance;
use App\Models\Inspection;
use App\Models\MaintenanceRequest;
use App\Models\Procurement;
use Livewire\Component;

/**
 * Dashboard Component
 * 
 * Provides a high-level operational overview of the Asset Management System.
 * This component is READ-ONLY and does not perform any state-changing operations.
 * 
 * Displays:
 * 1. System statistics (asset counts by status)
 * 2. Ongoing maintenance (assets currently unavailable)
 * 3. Borrowed assets (assets assigned to employees)
 * 4. Pending maintenance requests (awaiting admin action)
 * 5. Recent inspections (condition evaluation history)
 * 6. Recent procurements (acquisition history)
 */
class Dashboard extends Component
{
    /**
     * System Statistics
     */
    public int $totalAssets = 0;
    public int $activeAssets = 0;
    public int $borrowedAssetsCount = 0;
    public int $maintenanceAssets = 0;
    public int $disposedAssets = 0;

    /**
     * Mount the component and load all dashboard data
     */
    public function mount(): void
    {
        $this->loadStatistics();
    }

    /**
     * Load system statistics
     */
    private function loadStatistics(): void
    {
        $this->totalAssets = Asset::where('status', '!=', 'dihapuskan')->count();
        $this->activeAssets = Asset::where('status', 'aktif')->where('is_available', true)->count();
        $this->borrowedAssetsCount = Asset::where('status', 'dipinjam')->count();
        $this->maintenanceAssets = Asset::where('status', 'dipelihara')->count();
        $this->disposedAssets = Asset::where('status', 'dihapuskan')->count();
    }

    /**
     * Get ongoing maintenance records
     * Filter: status = 'dalam_proses' ONLY
     */
    public function getOngoingMaintenanceProperty()
    {
        return AssetMaintenance::with(['asset.location'])
            ->where('status', 'dalam_proses')
            ->orderBy('maintenance_date', 'asc')
            ->limit(10)
            ->get();
    }

    /**
     * Get currently borrowed assets
     * Filter: status = 'dipinjam'
     */
    public function getBorrowedAssetsProperty()
    {
        return AssetLoan::with(['asset', 'borrower'])
            ->where('status', 'dipinjam')
            ->orderBy('loan_date', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get pending maintenance requests
     * Filter: status = 'diajukan'
     */
    public function getPendingRequestsProperty()
    {
        return MaintenanceRequest::with(['asset', 'requester'])
            ->where('status', 'diajukan')
            ->orderBy('request_date', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent inspections
     * Order: inspected_at DESC
     * Limit: 5 records
     */
    public function getRecentInspectionsProperty()
    {
        return Inspection::with(['asset', 'inspector'])
            ->orderBy('inspected_at', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent procurements
     * Order: procurement_date DESC
     * Limit: 5 records
     */
    public function getRecentProcurementsProperty()
    {
        return Procurement::with(['category', 'supplier'])
            ->orderBy('procurement_date', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * Render the dashboard view
     */
    public function render()
    {
        return view('livewire.dashboard', [
            'ongoingMaintenance' => $this->ongoingMaintenance,
            'borrowedAssets' => $this->borrowedAssets,
            'pendingRequests' => $this->pendingRequests,
            'recentInspections' => $this->recentInspections,
            'recentProcurements' => $this->recentProcurements,
        ]);
    }
}
