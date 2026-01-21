<?php

namespace App\Livewire\Maintenance;

use App\Models\AssetMaintenance;
use App\Services\Maintenance\MaintenanceWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AssetMaintenancesManager extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public string $filterStatus = '';

    // Modal states
    public bool $showViewModal = false;
    public bool $showCompleteModal = false;
    public bool $showCancelModal = false;
    public ?AssetMaintenance $selectedMaintenance = null;
    public string $cancelReason = '';

    /**
     * Get all asset maintenance records
     */
    #[Computed]
    public function maintenances()
    {
        return AssetMaintenance::query()
            ->with([
                'asset:id,asset_code,name',
                'creator:id,name',
                'maintenanceRequest:id,status',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('asset', function ($query) {
                        $query->where('asset_code', 'like', "%{$this->search}%")
                              ->orWhere('name', 'like', "%{$this->search}%");
                    });
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->orderBy('maintenance_date', 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Open view modal
     */
    public function viewMaintenance(AssetMaintenance $maintenance): void
    {
        $this->selectedMaintenance = $maintenance->load(
            'asset',
            'creator',
            'maintenanceRequest'
        );
        $this->showViewModal = true;
    }

    /**
     * Open complete modal
     */
    public function openCompleteModal(AssetMaintenance $maintenance): void
    {
        if ($maintenance->status !== 'dalam_proses') {
            $this->dispatch('notify', type: 'error', message: 'Only in-progress maintenance can be marked as completed.');
            return;
        }

        $this->selectedMaintenance = $maintenance;
        $this->showCompleteModal = true;
    }

    /**
     * Mark maintenance as completed
     * 
     * Delegates business logic to MaintenanceWorkflowService.
     * This method is UI-only: validation, service call, error handling, feedback.
     * All state transitions (maintenance, request, asset) handled by service atomically.
     */
    public function completeMaintenance(): void
    {
        try {
            if (!$this->selectedMaintenance || $this->selectedMaintenance->status !== 'dalam_proses') {
                $this->dispatch('notify', type: 'error', message: 'Invalid maintenance state.');
                $this->closeModals();
                return;
            }

            // Reload to ensure fresh data
            $maintenance = AssetMaintenance::with('asset', 'maintenanceRequest')
                ->findOrFail($this->selectedMaintenance->id);

            // Validate state
            if ($maintenance->status !== 'dalam_proses') {
                $this->dispatch('notify', type: 'error', message: 'Only in-progress maintenance can be completed.');
                $this->closeModals();
                return;
            }

            // Delegate to service (handles all updates atomically)
            $service = new MaintenanceWorkflowService();
            $completed = $service->completeMaintenance($maintenance, Auth::user());

            $this->dispatch('notify', type: 'success', message: 'Maintenance completed. Asset restored to active.');
            $this->closeModals();
        } catch (\Exception $e) {
            // Service exceptions have context; show user-friendly message
            $this->dispatch('notify', type: 'error', message: 'Error completing maintenance: ' . $e->getMessage());
            report($e);
        }
    }

    /**
     * Close all modals
     */
    public function closeModals(): void
    {
        $this->showViewModal = false;
        $this->showCompleteModal = false;
        $this->showCancelModal = false;
        $this->selectedMaintenance = null;
        $this->cancelReason = '';
    }

    /**
     * Open cancel modal
     */
    public function openCancelModal(AssetMaintenance $maintenance): void
    {
        // Can only cancel if NOT completed and NOT already cancelled
        if ($maintenance->status === 'selesai') {
            $this->dispatch('notify', type: 'error', message: 'Completed maintenance cannot be cancelled.');
            return;
        }

        if ($maintenance->status === 'dibatalkan') {
            $this->dispatch('notify', type: 'error', message: 'This maintenance is already cancelled.');
            return;
        }

        $this->selectedMaintenance = $maintenance->load('asset', 'maintenanceRequest');
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    /**
     * Cancel maintenance
     * 
     * Delegates business logic to MaintenanceWorkflowService.
     * This method is UI-only: validation, service call, error handling, feedback.
     * All state transitions (maintenance, request, asset) handled by service atomically.
     */
    public function cancelMaintenance(): void
    {
        try {
            if (!$this->selectedMaintenance) {
                $this->dispatch('notify', type: 'error', message: 'No maintenance selected.');
                $this->closeModals();
                return;
            }

            // Reload to ensure fresh data
            $maintenance = AssetMaintenance::with('asset', 'maintenanceRequest')
                ->findOrFail($this->selectedMaintenance->id);

            // Validate state - cannot cancel completed or already cancelled
            if ($maintenance->status === 'selesai') {
                $this->dispatch('notify', type: 'error', message: 'Completed maintenance cannot be cancelled.');
                $this->closeModals();
                return;
            }

            if ($maintenance->status === 'dibatalkan') {
                $this->dispatch('notify', type: 'error', message: 'This maintenance is already cancelled.');
                $this->closeModals();
                return;
            }

            // Delegate to service (handles all updates atomically)
            $service = new MaintenanceWorkflowService();
            $cancelled = $service->cancelMaintenance($maintenance, Auth::user(), $this->cancelReason ?: null);

            $this->dispatch('notify', type: 'success', message: 'Maintenance cancelled. Asset restored to active.');
            $this->closeModals();
        } catch (\Exception $e) {
            // Service exceptions have context; show user-friendly message
            $this->dispatch('notify', type: 'error', message: 'Error cancelling maintenance: ' . $e->getMessage());
            report($e);
        }
    }

    public function render()
    {
        return view('livewire.maintenance.asset-maintenances-manager', [
            'maintenances' => $this->maintenances,
        ]);
    }
}
