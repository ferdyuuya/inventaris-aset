<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\AssetService;
use App\Services\AssetLocationService;
use App\Services\AssetBorrowingService;
use App\Services\AssetMaintenanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Redirect;

class AssetController extends Controller
{
    public function __construct(
        protected AssetService $assetService,
        protected AssetLocationService $locationService,
        protected AssetBorrowingService $borrowingService,
        protected AssetMaintenanceService $maintenanceService,
    ) {}

    /**
     * Display summary page with metrics
     */
    public function summary(): View
    {
        $metrics = $this->assetService->getSummaryMetrics();

        return view('assets.summary', compact('metrics'));
    }

    /**
     * Display paginated asset list
     */
    public function index(Request $request): View
    {
        $perPage = $request->query('per_page', 25);
        
        $filters = [
            'search' => $request->query('search', ''),
            'category_id' => $request->query('category_id', ''),
            'status' => $request->query('status', ''),
            'location_id' => $request->query('location_id', ''),
        ];

        $assets = $this->assetService->getAssetList($filters, $perPage);
        $categories = \App\Models\AssetCategory::all();
        $locations = \App\Models\Location::all();

        return view('assets.index', compact('assets', 'categories', 'locations', 'filters'));
    }

    /**
     * Display asset detail page
     */
    public function show(Asset $asset): View
    {
        $asset->load(['category', 'location', 'supplier', 'transactions', 'loans', 'maintenances']);

        $availableActions = $this->assetService->getAvailableActions($asset);
        $locationHistory = $this->locationService->getLocationHistory($asset);
        $borrowingHistory = $this->borrowingService->getBorrowingHistory($asset);
        $maintenanceHistory = $this->maintenanceService->getMaintenanceHistory($asset);
        $currentBorrower = $this->borrowingService->getCurrentBorrower($asset);
        $currentMaintenance = $this->maintenanceService->getCurrentMaintenance($asset);

        return view('assets.show', compact(
            'asset',
            'availableActions',
            'locationHistory',
            'borrowingHistory',
            'maintenanceHistory',
            'currentBorrower',
            'currentMaintenance',
        ));
    }

    /**
     * Transfer asset to new location
     */
    public function transferLocation(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->locationService->transferAsset(
                $asset,
                $validated['location_id'],
                $validated['reason']
            );

            return redirect()->route('assets.show', $asset)
                ->with('success', 'Asset transferred successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to transfer asset: ' . $e->getMessage());
        }
    }

    /**
     * Borrow an asset
     */
    public function borrow(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'expected_return_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $this->borrowingService->borrowAsset(
                $asset,
                $validated['employee_id'],
                $validated['expected_return_date'] ?? null
            );

            return redirect()->route('assets.show', $asset)
                ->with('success', 'Asset borrowed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to borrow asset: ' . $e->getMessage());
        }
    }

    /**
     * Return a borrowed asset
     */
    public function returnAsset(Request $request, Asset $asset)
    {
        try {
            $loan = $asset->activeLoan();
            if (!$loan) {
                return redirect()->back()
                    ->with('error', 'Asset is not currently borrowed');
            }

            $this->borrowingService->returnAsset($loan->id);

            return redirect()->route('assets.show', $asset)
                ->with('success', 'Asset returned successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to return asset: ' . $e->getMessage());
        }
    }

    /**
     * Send asset to maintenance
     */
    public function sendMaintenance(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'estimated_completion_date' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $this->maintenanceService->sendToMaintenance(
                $asset,
                $validated['reason'],
                $validated['estimated_completion_date'] ?? null
            );

            return redirect()->route('assets.show', $asset)
                ->with('success', 'Asset sent to maintenance successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to send asset to maintenance: ' . $e->getMessage());
        }
    }

    /**
     * Complete maintenance on an asset
     */
    public function completeMaintenance(Request $request, Asset $asset)
    {
        try {
            $maintenance = $asset->activeMaintenance();
            if (!$maintenance) {
                return redirect()->back()
                    ->with('error', 'Asset is not currently under maintenance');
            }

            $this->maintenanceService->completeMaintenance($maintenance->id);

            return redirect()->route('assets.show', $asset)
                ->with('success', 'Maintenance completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to complete maintenance: ' . $e->getMessage());
        }
    }
}
