<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceRequest;
use App\Models\Asset;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class MaintenanceRequestsManager extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';

    // Modal states
    public bool $showViewModal = false;
    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public ?MaintenanceRequest $selectedRequest = null;
    public string $rejectReason = '';

    /**
     * Get all maintenance requests with default ordering
     */
    #[Computed]
    public function requests()
    {
        return MaintenanceRequest::query()
            ->with([
                'asset:id,asset_code,name',
                'requester:id,name',
                'approver:id,name',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('asset', function ($query) {
                        $query->where('asset_code', 'like', "%{$this->search}%")
                              ->orWhere('name', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('requester', function ($query) {
                        $query->where('name', 'like', "%{$this->search}%");
                    });
                });
            })
            ->orderedByStatus()
            ->paginate($this->perPage);
    }

    /**
     * Open view modal
     */
    public function viewRequest(MaintenanceRequest $request): void
    {
        $this->selectedRequest = $request->load('asset', 'requester', 'approver');
        $this->showViewModal = true;
    }

    /**
     * Open approve modal
     */
    public function openApproveModal(MaintenanceRequest $request): void
    {
        if ($request->status !== 'diajukan') {
            $this->dispatch('notify', type: 'error', message: 'Only pending requests can be approved.');
            return;
        }

        $this->selectedRequest = $request;
        $this->showApproveModal = true;
    }

    /**
     * Approve a maintenance request
     * - Updates status to disetujui
     * - Auto-generates asset maintenance record
     */
    public function approveRequest(): void
    {
        if (!$this->selectedRequest || $this->selectedRequest->status !== 'diajukan') {
            $this->dispatch('notify', type: 'error', message: 'Invalid request state.');
            $this->closeModals();
            return;
        }

        try {
            $request = $this->selectedRequest;

            // Update request status
            $request->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
            ]);

            // Auto-generate asset maintenance record
            \App\Models\AssetMaintenance::create([
                'asset_id' => $request->asset_id,
                'maintenance_request_id' => $request->id,
                'maintenance_date' => $request->request_date,
                'estimated_completion_date' => $request->request_date->addDays(7),
                'description' => $request->issue_description,
                'status' => 'dalam_proses',
                'created_by' => auth()->id(),
            ]);

            // Update asset status to dipelihara
            $request->asset->update(['status' => 'dipelihara']);

            $this->dispatch('notify', type: 'success', message: 'Maintenance request approved and maintenance record created.');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error approving request: ' . $e->getMessage());
        }
    }

    /**
     * Open reject modal
     */
    public function openRejectModal(MaintenanceRequest $request): void
    {
        if ($request->status !== 'diajukan') {
            $this->dispatch('notify', type: 'error', message: 'Only pending requests can be rejected.');
            return;
        }

        $this->selectedRequest = $request;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    /**
     * Reject a maintenance request
     * - Updates status to ditolak
     * - Does NOT create asset maintenance
     */
    public function rejectRequest(): void
    {
        if (!$this->selectedRequest || $this->selectedRequest->status !== 'diajukan') {
            $this->dispatch('notify', type: 'error', message: 'Invalid request state.');
            $this->closeModals();
            return;
        }

        try {
            $this->selectedRequest->update([
                'status' => 'ditolak',
                'approved_by' => auth()->id(),
            ]);

            $this->dispatch('notify', type: 'success', message: 'Maintenance request rejected.');
            $this->closeModals();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error rejecting request: ' . $e->getMessage());
        }
    }

    /**
     * Close all modals
     */
    public function closeModals(): void
    {
        $this->showViewModal = false;
        $this->showApproveModal = false;
        $this->showRejectModal = false;
        $this->selectedRequest = null;
        $this->rejectReason = '';
    }

    public function render()
    {
        return view('livewire.maintenance.maintenance-requests-manager', [
            'requests' => $this->requests,
        ]);
    }
}
