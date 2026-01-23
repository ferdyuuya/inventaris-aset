<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use BaconQrCode\Writer;
use Livewire\Component;
use App\Models\Employee;
use App\Models\Location;
use App\Models\MaintenanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\AssetService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use App\Services\AssetLocationService;
use App\Services\AssetLoanService;
use BaconQrCode\Renderer\GDLibRenderer;
use App\Services\AssetMaintenanceService;
use App\Services\AssetDisposalService;
use App\Services\InspectionService;
use Illuminate\Support\Facades\Log;

class AssetDetail extends Component
{
    public Asset $asset;
    public string $activeTab = 'details';
    
    public bool $showTransferModal = false;
    public bool $showBorrowModal = false;
    public bool $showReturnModal = false;
    public bool $showMaintenanceModal = false;
    public bool $showRequestMaintenanceModal = false;
    public bool $showDisposeModal = false;
    public bool $showInspectModal = false;
    public bool $showMaintenanceDetailModal = false;

    // Selected maintenance record for detail modal
    public ?\App\Models\AssetMaintenance $selectedMaintenanceRecord = null;

    // Transfer Location form
    public ?int $transferLocationId = null;
    public ?string $transferDate = null;
    public string $transferNotes = '';

    // Borrow Asset form
    public ?int $borrowEmployeeId = null;
    public ?string $borrowDate = null;
    public ?string $borrowExpectedReturnDate = null;
    public string $borrowNotes = '';

    // Return Asset form
    public ?string $returnDate = null;
    public string $returnCondition = 'baik';
    public string $returnNotes = '';

    // Maintenance form
    public string $maintenanceReason = '';
    public ?string $maintenanceEstimatedDate = null;

    // Quick Maintenance Request form
    #[Validate('required|string|min:5|max:500')]
    public string $requestMaintenanceDescription = '';

    // Dispose Asset form
    #[Validate('required|string|min:5|max:500')]
    public string $disposeReason = '';

    // Inspect Asset form
    public string $inspectCondition = '';
    public string $inspectDescription = '';

    // QR Code
    public ?string $qrCodeBase64 = null;

    public function mount(Asset $asset)
    {
        $this->asset = $asset->load(['category', 'location', 'supplier']);
        $this->generateQRCode();
    }

    /**
     * Generate QR code for the asset
     */
    public function generateQRCode(): void
    {
        try {
            $renderer = new GDLibRenderer(200);
            $writer = new Writer($renderer);
            $qrCodeImage = $writer->writeString($this->asset->asset_code);
            
            // Convert to base64 for inline display
            $this->qrCodeBase64 = 'data:image/png;base64,' . base64_encode($qrCodeImage);
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            $this->qrCodeBase64 = null;
        }
    }

    /**
     * Download QR code as PNG file
     */
    public function downloadQRCode()
    {
        try {
            $renderer = new GDLibRenderer(200);
            $writer = new Writer($renderer);
            $qrCodeImage = $writer->writeString($this->asset->asset_code);
            
            return response($qrCodeImage)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $this->asset->asset_code . '.png"');
        } catch (\Exception $e) {
            Log::error('QR Code download failed: ' . $e->getMessage());
            $this->dispatch('notify-error', 'Failed to download QR code');
        }
    }

    /**
     * Get available actions for this asset
     */
    #[Computed]
    public function availableActions()
    {
        return app(AssetService::class)->getAvailableActions($this->asset);
    }

    /**
     * Get location history
     */
    #[Computed]
    public function locationHistory()
    {
        return app(AssetLocationService::class)->getLocationHistory($this->asset);
    }

    /**
     * Get borrowing history
     */
    #[Computed]
    public function borrowingHistory()
    {
        return app(AssetLoanService::class)->getBorrowingHistory($this->asset);
    }

    /**
     * Get maintenance history
     */
    #[Computed]
    public function maintenanceHistory()
    {
        return app(AssetMaintenanceService::class)->getMaintenanceHistory($this->asset);
    }

    /**
     * Get current active loan
     */
    #[Computed]
    public function activeLoan()
    {
        return app(AssetLoanService::class)->getActiveLoan($this->asset);
    }

    /**
     * Check if asset can be borrowed
     */
    #[Computed]
    public function canBorrow(): bool
    {
        return app(AssetLoanService::class)->canBorrow($this->asset);
    }

    /**
     * Get current maintenance
     */
    #[Computed]
    public function currentMaintenance()
    {
        return app(AssetMaintenanceService::class)->getCurrentMaintenance($this->asset);
    }

    /**
     * Get all employees for borrowing select
     */
    #[Computed]
    public function employees()
    {
        return Employee::all();
    }

    /**
     * Get all locations for transfer select
     */
    #[Computed]
    public function locations()
    {
        return Location::where('id', '!=', $this->asset->location_id)->get();
    }

    /**
     * Set active tab
     */
    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    /**
     * Open transfer location modal
     */
    public function openTransferModal(): void
    {
        $this->transferDate = now()->toDateString();
        $this->showTransferModal = true;
    }

    /**
     * Close transfer location modal
     */
    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->transferLocationId = null;
        $this->transferDate = null;
        $this->transferNotes = '';
    }

    /**
     * Submit transfer location
     */
    public function submitTransfer(): void
    {
        $this->validate([
            'transferLocationId' => 'required|exists:locations,id',
            'transferDate' => 'required|date',
            'transferNotes' => 'nullable|string|max:500',
        ]);

        try {
            app(AssetLocationService::class)->transferAsset(
                $this->asset,
                $this->transferLocationId,
                $this->transferNotes
            );

            $this->asset->refresh();
            $this->dispatch('notify', 'Asset transferred successfully');
            $this->closeTransferModal();
        } catch (\Exception $e) {
            $this->dispatch('notify-error', 'Transfer failed: ' . $e->getMessage());
        }
    }

    /**
     * Open borrow asset modal
     */
    public function openBorrowModal(): void
    {
        if (!$this->canBorrow) {
            $this->dispatch('notify', type: 'error', message: 'This asset cannot be borrowed.');
            return;
        }
        
        $this->borrowEmployeeId = null;
        $this->borrowDate = now()->toDateString();
        $this->borrowExpectedReturnDate = null;
        $this->borrowNotes = '';
        $this->showBorrowModal = true;
    }

    /**
     * Close borrow asset modal
     */
    public function closeBorrowModal(): void
    {
        $this->showBorrowModal = false;
        $this->borrowEmployeeId = null;
        $this->borrowDate = null;
        $this->borrowExpectedReturnDate = null;
        $this->borrowNotes = '';
        $this->resetValidation(['borrowEmployeeId', 'borrowDate', 'borrowExpectedReturnDate', 'borrowNotes']);
    }

    /**
     * Submit borrow asset
     */
    public function submitBorrow(): void
    {
        $this->validate([
            'borrowEmployeeId' => 'required|exists:employees,id',
            'borrowDate' => 'required|date',
            'borrowExpectedReturnDate' => 'nullable|date|after_or_equal:borrowDate',
            'borrowNotes' => 'nullable|string|max:1000',
        ]);

        try {
            $employee = \App\Models\Employee::findOrFail($this->borrowEmployeeId);
            
            app(AssetLoanService::class)->createLoan(
                $this->asset,
                $employee,
                [
                    'borrow_date' => $this->borrowDate,
                    'expected_return_date' => $this->borrowExpectedReturnDate,
                    'notes' => $this->borrowNotes,
                ]
            );

            $this->asset->refresh();
            $this->dispatch('notify', type: 'success', message: 'Asset borrowed successfully.');
            $this->closeBorrowModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Borrow failed: ' . $e->getMessage());
        }
    }

    /**
     * Open return asset modal
     */
    public function openReturnModal(): void
    {
        $activeLoan = $this->activeLoan;
        if (!$activeLoan) {
            $this->dispatch('notify', type: 'error', message: 'No active loan found for this asset.');
            return;
        }

        $this->returnDate = now()->toDateString();
        $this->returnCondition = 'baik';
        $this->returnNotes = '';
        $this->showReturnModal = true;
    }

    /**
     * Close return asset modal
     */
    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->returnDate = null;
        $this->returnCondition = 'baik';
        $this->returnNotes = '';
        $this->resetValidation(['returnDate', 'returnCondition', 'returnNotes']);
    }

    /**
     * Submit return asset
     */
    public function submitReturn(): void
    {
        $this->validate([
            'returnDate' => 'required|date',
            'returnCondition' => 'required|in:baik,rusak',
            'returnNotes' => 'nullable|string|max:1000',
        ]);

        try {
            $activeLoan = $this->activeLoan;
            if (!$activeLoan) {
                throw new \Exception('No active loan found for this asset.');
            }

            app(AssetLoanService::class)->finishLoan(
                $activeLoan,
                [
                    'return_date' => $this->returnDate,
                    'condition_after_return' => $this->returnCondition,
                    'notes' => $this->returnNotes,
                ]
            );

            $this->asset->refresh();
            $this->dispatch('notify', type: 'success', message: 'Asset returned successfully.');
            $this->closeReturnModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Return failed: ' . $e->getMessage());
        }
    }

    /**
     * Open maintenance modal
     */
    public function openMaintenanceModal(): void
    {
        $this->showMaintenanceModal = true;
        $this->resetExcept('asset', 'activeTab', 'showTransferModal', 'showBorrowModal', 'showRequestMaintenanceModal');
    }

    /**
     * Close maintenance modal
     */
    public function closeMaintenanceModal(): void
    {
        $this->showMaintenanceModal = false;
        $this->maintenanceReason = '';
        $this->maintenanceEstimatedDate = null;
    }

    /**
     * Open quick maintenance request modal
     */
    public function openRequestMaintenanceModal(): void
    {
        $this->resetRequestMaintenanceForm();
        $this->showRequestMaintenanceModal = true;
    }

    /**
     * Close quick maintenance request modal
     */
    public function closeRequestMaintenanceModal(): void
    {
        $this->showRequestMaintenanceModal = false;
        $this->resetRequestMaintenanceForm();
    }

    /**
     * Reset maintenance request form
     */
    public function resetRequestMaintenanceForm(): void
    {
        $this->requestMaintenanceDescription = '';
        $this->resetValidation();
    }

    /**
     * Submit quick maintenance request for current asset
     */
    public function submitRequestMaintenance(): void
    {
        $validated = $this->validate();

        try {
            $user = Auth::user();

            if (!$user) {
                $this->dispatch('notify', 'error', 'You must be logged in to request maintenance.');
                return;
            }

            MaintenanceRequest::create([
                'asset_id' => $this->asset->id,
                'requested_by' => $user->id,
                'request_date' => now()->toDateString(),
                'issue_description' => $validated['requestMaintenanceDescription'],
                'status' => 'diajukan',
            ]);

            $this->dispatch('notify', 'Maintenance request submitted successfully.');
            $this->closeRequestMaintenanceModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', 'error', 'Error creating maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Submit send to maintenance
     */
    public function submitMaintenance(): void
    {
        $this->validate([
            'maintenanceReason' => 'required|string|max:500',
            'maintenanceEstimatedDate' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            app(AssetMaintenanceService::class)->sendToMaintenance(
                $this->asset,
                $this->maintenanceReason,
                $this->maintenanceEstimatedDate
            );

            $this->asset->refresh();
            $this->dispatch('notify', 'Asset sent to maintenance successfully');
            $this->closeMaintenanceModal();
        } catch (\Exception $e) {
            $this->dispatch('notify-error', 'Maintenance submission failed: ' . $e->getMessage());
        }
    }

    /**
     * Complete maintenance
     */
    public function completeMaintenance(): void
    {
        try {
            $maintenance = $this->currentMaintenance;
            if (!$maintenance) {
                throw new \Exception('Asset is not currently under maintenance');
            }

            app(AssetMaintenanceService::class)->completeMaintenance($maintenance->id);
            $this->asset->refresh();
            $this->dispatch('notify', 'Maintenance completed successfully');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', 'Maintenance completion failed: ' . $e->getMessage());
        }
    }

    /**
     * Open dispose asset modal
     */
    public function openDisposeModal(): void
    {
        // Check if asset can be disposed
        $check = app(AssetDisposalService::class)->canDispose($this->asset);
        
        if (!$check['can_dispose']) {
            $this->dispatch('notify', type: 'error', message: $check['reason']);
            return;
        }

        $this->disposeReason = '';
        $this->showDisposeModal = true;
    }

    /**
     * Close dispose asset modal
     */
    public function closeDisposeModal(): void
    {
        $this->showDisposeModal = false;
        $this->disposeReason = '';
        $this->resetValidation('disposeReason');
    }

    /**
     * Submit asset disposal
     * 
     * Delegates business logic to AssetDisposalService.
     * This method is UI-only: validation, service call, error handling, feedback.
     * All state transitions handled by service atomically.
     */
    public function submitDispose(): void
    {
        $this->validate([
            'disposeReason' => 'required|string|min:5|max:500',
        ]);

        try {
            $user = Auth::user();

            if (!$user) {
                $this->dispatch('notify', type: 'error', message: 'You must be logged in to dispose assets.');
                return;
            }

            // Delegate to service (handles all updates atomically)
            app(AssetDisposalService::class)->dispose(
                $this->asset,
                $user,
                $this->disposeReason
            );

            $this->asset->refresh();
            $this->dispatch('notify', type: 'success', message: 'Asset disposed successfully. This action is irreversible.');
            $this->closeDisposeModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Disposal failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if asset can be disposed (for UI visibility)
     */
    #[Computed]
    public function canDispose(): bool
    {
        return $this->asset->canBeDisposed();
    }

    /**
     * Get disposal record if asset is disposed
     */
    #[Computed]
    public function disposalRecord()
    {
        return app(AssetDisposalService::class)->getDisposalRecord($this->asset);
    }

    /**
     * Check if asset can be inspected
     */
    #[Computed]
    public function canInspect(): bool
    {
        return !$this->asset->isDisposed();
    }

    /**
     * Get inspection history for this asset
     */
    #[Computed]
    public function inspectionHistory()
    {
        return app(InspectionService::class)->getInspectionHistory($this->asset);
    }

    /**
     * Open inspect modal
     */
    public function openInspectModal(): void
    {
        if ($this->asset->isDisposed()) {
            $this->dispatch('notify', type: 'error', message: 'Cannot inspect a disposed asset.');
            return;
        }

        $this->inspectCondition = '';
        $this->inspectDescription = '';
        $this->showInspectModal = true;
    }

    /**
     * Close inspect modal
     */
    public function closeInspectModal(): void
    {
        $this->showInspectModal = false;
        $this->inspectCondition = '';
        $this->inspectDescription = '';
        $this->resetValidation(['inspectCondition', 'inspectDescription']);
    }

    /**
     * View maintenance detail modal
     */
    public function viewMaintenanceDetail(int $maintenanceId): void
    {
        $this->selectedMaintenanceRecord = \App\Models\AssetMaintenance::with(['creator', 'maintenanceRequest'])
            ->find($maintenanceId);
        $this->showMaintenanceDetailModal = true;
    }

    /**
     * Close maintenance detail modal
     */
    public function closeMaintenanceDetailModal(): void
    {
        $this->showMaintenanceDetailModal = false;
        $this->selectedMaintenanceRecord = null;
    }

    /**
     * Submit asset inspection
     * 
     * Delegates business logic to InspectionService.
     * Updates asset.condition ONLY - does NOT change status or availability.
     */
    public function submitInspection(): void
    {
        $this->validate([
            'inspectCondition' => 'required|in:baik,rusak,perlu_perbaikan',
            'inspectDescription' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();

            if (!$user) {
                $this->dispatch('notify', type: 'error', message: 'You must be logged in to perform inspections.');
                return;
            }

            // Delegate to service (handles atomic transaction)
            app(InspectionService::class)->createInspection(
                $this->asset,
                $this->inspectCondition,
                $this->inspectDescription ?: null,
                $user
            );

            $this->asset->refresh();
            $this->dispatch('notify', type: 'success', message: 'Inspection recorded. Asset condition updated.');
            $this->closeInspectModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Inspection failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.assets.asset-detail', [
            'availableActions' => $this->availableActions,
            'locationHistory' => $this->locationHistory,
            'borrowingHistory' => $this->borrowingHistory,
            'maintenanceHistory' => $this->maintenanceHistory,
            'activeLoan' => $this->activeLoan,
            'currentMaintenance' => $this->currentMaintenance,
            'employees' => $this->employees,
            'locations' => $this->locations,
            'qrCodeBase64' => $this->qrCodeBase64,
            'canBorrow' => $this->canBorrow,
            'canDispose' => $this->canDispose,
            'disposalRecord' => $this->disposalRecord,
            'canInspect' => $this->canInspect,
            'inspectionHistory' => $this->inspectionHistory,
        ]);
    }
}
