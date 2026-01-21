<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceRequest;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Services\Maintenance\MaintenanceWorkflowService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

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

    // Create maintenance request form
    #[Validate('required|integer|exists:assets,id')]
    public ?int $createAssetId = null;

    #[Validate('required|string|min:5|max:500')]
    public string $createDescription = '';

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
     * 
     * Delegates business logic to MaintenanceWorkflowService.
     * This method is UI-only: validation, service call, error handling, feedback.
     * All state transitions handled by service in atomic transaction.
     */
    public function approveRequest($requestId): void
    {
        try {
            $request = MaintenanceRequest::with('asset')->findOrFail($requestId);

            // Validate request status for user feedback
            if ($request->status !== 'diajukan') {
                $this->dispatch('notify', type: 'error', message: 'Only pending requests can be approved.');
                $this->closeModals();
                return;
            }

            // Delegate to service (handles transaction, all updates, atomicity)
            $service = new MaintenanceWorkflowService();
            $maintenance = $service->approveRequest($request, Auth::user());

            $this->dispatch('notify', 
                type: 'success', 
                message: 'Maintenance request approved. Asset maintenance record created.'
            );
            $this->closeModals();
        } catch (\Exception $e) {
            // Service exceptions have context; show user-friendly message
            $this->dispatch('notify', type: 'error', message: 'Error approving request: ' . $e->getMessage());
            report($e);
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
    public function rejectRequest($requestId): void
    {
        $request = MaintenanceRequest::findOrFail($requestId);

        if ($request->status !== 'diajukan') {
            $this->dispatch('notify', type: 'error', message: 'Only pending requests can be rejected.');
            $this->closeModals();
            return;
        }

        try {
            $request->update([
                'status' => 'ditolak',
                'approved_by' => Auth::user()->id,
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
        $this->rejectReason = '';
        $this->resetCreateForm();
    }

    /**
     * Reset create maintenance request form
     */
    public function resetCreateForm(): void
    {
        $this->createAssetId = null;
        $this->createDescription = '';
        $this->resetValidation();
    }

    /**
     * Submit create maintenance request form
     */
    public function submitCreateMaintenance(): void
    {
        $validated = $this->validate();

        try {
            $user = Auth::user();

            if (!$user) {
                $this->dispatch('notify', type: 'error', message: 'You must be logged in to create a maintenance request.');
                return;
            }

            MaintenanceRequest::create([
                'asset_id' => $validated['createAssetId'],
                'requested_by' => $user->id,
                'request_date' => now()->toDateString(),
                'issue_description' => $validated['createDescription'],
                'status' => 'diajukan',
            ]);

            $this->dispatch('notify', type: 'success', message: 'Maintenance request submitted successfully.');
            $this->closeModals();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Error creating maintenance request: ' . $e->getMessage());
        }
    }

    /**
     * Get all available assets for dropdown
     */
    #[Computed]
    public function availableAssets()
    {
        return Asset::query()
            ->where('status', 'available')
            ->orderBy('asset_code', 'asc')
            ->get(['id', 'asset_code', 'name']);
    }

    public function render()
    {
        return view('livewire.maintenance.maintenance-requests-manager', [
            'requests' => $this->requests,
            'availableAssets' => $this->availableAssets,
        ]);
    }
}
