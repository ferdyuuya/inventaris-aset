<?php

namespace App\Livewire\Maintenance;

use App\Models\AssetMaintenance;
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
    public ?AssetMaintenance $selectedMaintenance = null;

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
     * - Sets completed_date
     * - Updates maintenance status to selesai
     * - Updates related maintenance request status to selesai
     * - Updates asset status back to available
     */
    public function completeMaintenance(): void
    {
        if (!$this->selectedMaintenance || $this->selectedMaintenance->status !== 'dalam_proses') {
            $this->dispatch('notify', type: 'error', message: 'Invalid maintenance state.');
            $this->closeModals();
            return;
        }

        try {
            $maintenance = $this->selectedMaintenance;

            // Update maintenance record
            $maintenance->update([
                'completed_date' => now()->toDateString(),
                'status' => 'selesai',
            ]);

            // Update related maintenance request status
            if ($maintenance->maintenanceRequest) {
                $maintenance->maintenanceRequest->update(['status' => 'selesai']);
            }

            // Update asset status back to available
            if ($maintenance->asset) {
                $maintenance->asset->update(['status' => 'available']);
            }

            $this->dispatch('notify', type: 'success', message: 'Maintenance marked as completed.');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error completing maintenance: ' . $e->getMessage());
        }
    }

    /**
     * Close all modals
     */
    public function closeModals(): void
    {
        $this->showViewModal = false;
        $this->showCompleteModal = false;
        $this->selectedMaintenance = null;
    }

    public function render()
    {
        return view('livewire.maintenance.asset-maintenances-manager', [
            'maintenances' => $this->maintenances,
        ]);
    }
}
