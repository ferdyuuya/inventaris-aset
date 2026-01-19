<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Location;
use App\Services\AssetService;
use App\Services\AssetLocationService;
use App\Services\AssetBorrowingService;
use App\Services\AssetMaintenanceService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class AssetDetail extends Component
{
    public Asset $asset;
    public string $activeTab = 'details';
    
    public bool $showTransferModal = false;
    public bool $showBorrowModal = false;
    public bool $showMaintenanceModal = false;

    // Transfer Location form
    public ?int $transferLocationId = null;
    public string $transferReason = '';

    // Borrow Asset form
    public ?int $borrowEmployeeId = null;
    public ?string $borrowReturnDate = null;

    // Maintenance form
    public string $maintenanceReason = '';
    public ?string $maintenanceEstimatedDate = null;

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
            \Log::error('QR Code generation failed: ' . $e->getMessage());
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
            \Log::error('QR Code download failed: ' . $e->getMessage());
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
        return app(AssetBorrowingService::class)->getBorrowingHistory($this->asset);
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
     * Get current borrower
     */
    #[Computed]
    public function currentBorrower()
    {
        return app(AssetBorrowingService::class)->getCurrentBorrower($this->asset);
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
        $this->showTransferModal = true;
        $this->resetExcept('asset', 'activeTab', 'showBorrowModal', 'showMaintenanceModal');
    }

    /**
     * Close transfer location modal
     */
    public function closeTransferModal(): void
    {
        $this->showTransferModal = false;
        $this->transferLocationId = null;
        $this->transferReason = '';
    }

    /**
     * Submit transfer location
     */
    public function submitTransfer(): void
    {
        $this->validate([
            'transferLocationId' => 'required|exists:locations,id',
            'transferReason' => 'required|string|max:500',
        ]);

        try {
            app(AssetLocationService::class)->transferAsset(
                $this->asset,
                $this->transferLocationId,
                $this->transferReason
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
        $this->showBorrowModal = true;
        $this->resetExcept('asset', 'activeTab', 'showTransferModal', 'showMaintenanceModal');
    }

    /**
     * Close borrow asset modal
     */
    public function closeBorrowModal(): void
    {
        $this->showBorrowModal = false;
        $this->borrowEmployeeId = null;
        $this->borrowReturnDate = null;
    }

    /**
     * Submit borrow asset
     */
    public function submitBorrow(): void
    {
        $this->validate([
            'borrowEmployeeId' => 'required|exists:employees,id',
            'borrowReturnDate' => 'nullable|date|after_or_equal:today',
        ]);

        try {
            app(AssetBorrowingService::class)->borrowAsset(
                $this->asset,
                $this->borrowEmployeeId,
                $this->borrowReturnDate
            );

            $this->asset->refresh();
            $this->dispatch('notify', 'Asset borrowed successfully');
            $this->closeBorrowModal();
        } catch (\Exception $e) {
            $this->dispatch('notify-error', 'Borrow failed: ' . $e->getMessage());
        }
    }

    /**
     * Return borrowed asset
     */
    public function returnAsset(): void
    {
        try {
            $loan = $this->currentBorrower;
            if (!$loan) {
                throw new \Exception('Asset is not currently borrowed');
            }

            app(AssetBorrowingService::class)->returnAsset($loan->id);
            $this->asset->refresh();
            $this->dispatch('notify', 'Asset returned successfully');
        } catch (\Exception $e) {
            $this->dispatch('notify-error', 'Return failed: ' . $e->getMessage());
        }
    }

    /**
     * Open maintenance modal
     */
    public function openMaintenanceModal(): void
    {
        $this->showMaintenanceModal = true;
        $this->resetExcept('asset', 'activeTab', 'showTransferModal', 'showBorrowModal');
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

    public function render()
    {
        return view('livewire.assets.asset-detail', [
            'availableActions' => $this->availableActions,
            'locationHistory' => $this->locationHistory,
            'borrowingHistory' => $this->borrowingHistory,
            'maintenanceHistory' => $this->maintenanceHistory,
            'currentBorrower' => $this->currentBorrower,
            'currentMaintenance' => $this->currentMaintenance,
            'employees' => $this->employees,
            'locations' => $this->locations,
            'qrCodeBase64' => $this->qrCodeBase64,
        ]);
    }
}
