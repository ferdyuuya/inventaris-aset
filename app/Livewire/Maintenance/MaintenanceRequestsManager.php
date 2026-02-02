<?php

namespace App\Livewire\Maintenance;

use App\Models\MaintenanceRequest;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use App\Models\Employee;
use App\Services\Maintenance\MaintenanceWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
    public bool $showCreateModal = false;
    public bool $showRequestSuccessModal = false;
    public bool $showApprovalSuccessModal = false;
    public ?MaintenanceRequest $selectedRequest = null;
    public string $rejectReason = '';

    // PIC selection for approval (employee)
    public ?int $selectedPicEmployeeId = null;
    
    // Store the request ID explicitly for approval (avoid re-render issues)
    public ?int $approvalRequestId = null;

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
        $this->selectedRequest = $request->load('asset', 'requester', 'approver', 'maintenance.pic');
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

        $this->selectedRequest = $request->load('asset');
        $this->approvalRequestId = $request->id; // Store ID explicitly
        $this->selectedPicEmployeeId = null;
        $this->showApproveModal = true;
        
        Log::info('openApproveModal CALLED', [
            'request_id' => $request->id,
            'approvalRequestId' => $this->approvalRequestId,
        ]);
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
        // LOGGING: Prove this method is being called
        Log::info('approveRequest CALLED', [
            'requestId' => $requestId,
            'selectedPicEmployeeId' => $this->selectedPicEmployeeId,
            'user_id' => Auth::id(),
        ]);

        // FAIL-FAST: Validate request ID is provided
        if (empty($requestId)) {
            Log::error('approveRequest FAILED: No requestId provided');
            $this->addError('selectedPicEmployeeId', 'Invalid request. Please close and try again.');
            return;
        }

        // FAIL-FAST: Validate PIC employee selection is required
        if (empty($this->selectedPicEmployeeId)) {
            Log::warning('approveRequest FAILED: No PIC selected');
            $this->addError('selectedPicEmployeeId', 'Please select an employee as Person In Charge (PIC) before approving.');
            return;
        }

        try {
            $request = MaintenanceRequest::with('asset')->findOrFail($requestId);
            Log::info('approveRequest: Found request', ['request_id' => $request->id, 'status' => $request->status]);

            // Validate request status for user feedback
            if ($request->status !== 'diajukan') {
                Log::warning('approveRequest FAILED: Invalid status', ['status' => $request->status]);
                $this->addError('selectedPicEmployeeId', 'Only pending requests can be approved. Current status: ' . $request->status);
                return;
            }

            // Delegate to service (handles transaction, all updates, atomicity)
            $service = new MaintenanceWorkflowService();
            $maintenance = $service->approveRequest($request, Auth::user(), $this->selectedPicEmployeeId);

            Log::info('approveRequest SUCCESS', [
                'request_id' => $request->id,
                'maintenance_id' => $maintenance->id,
                'pic_employee_id' => $this->selectedPicEmployeeId,
            ]);

            // Show success feedback and close modal
            $this->showApproveModal = false;
            $this->showApprovalSuccessModal = true;
            $this->selectedPicEmployeeId = null;
        } catch (\Exception $e) {
            Log::error('approveRequest EXCEPTION', [
                'requestId' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('selectedPicEmployeeId', 'Error: ' . $e->getMessage());
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
     * Close all modals (except success confirmation)
     */
    public function closeModals(): void
    {
        $this->showViewModal = false;
        $this->showApproveModal = false;
        $this->showRejectModal = false;
        $this->showCreateModal = false;
        $this->rejectReason = '';
        $this->selectedPicEmployeeId = null;
        $this->approvalRequestId = null;
        $this->resetCreateForm();
    }

    /**
     * Close success confirmation modal
     */
    public function closeSuccessModal(): void
    {
        $this->showRequestSuccessModal = false;
    }

    /**
     * Close approval success confirmation modal
     */
    public function closeApprovalSuccessModal(): void
    {
        $this->showApprovalSuccessModal = false;
    }

    /**
     * Open create maintenance request modal
     */
    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
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
        // LOGGING: Prove this method is being called
        Log::info('submitCreateMaintenance CALLED', [
            'createAssetId' => $this->createAssetId,
            'createDescription' => $this->createDescription,
            'user_id' => Auth::id(),
        ]);

        // Explicit validation with rules
        $validated = $this->validate([
            'createAssetId' => 'required|integer|exists:assets,id',
            'createDescription' => 'required|string|min:5|max:500',
        ]);

        try {
            $user = Auth::user();

            if (!$user) {
                Log::error('submitCreateMaintenance FAILED: No authenticated user');
                $this->addError('createAssetId', 'You must be logged in to create a maintenance request.');
                return;
            }

            $maintenanceRequest = MaintenanceRequest::create([
                'asset_id' => $validated['createAssetId'],
                'requested_by' => $user->id,
                'request_date' => now()->toDateString(),
                'issue_description' => $validated['createDescription'],
                'status' => 'diajukan',
            ]);

            Log::info('submitCreateMaintenance SUCCESS', [
                'maintenance_request_id' => $maintenanceRequest->id,
                'asset_id' => $validated['createAssetId'],
            ]);

            // Close create modal and show success confirmation
            $this->showCreateModal = false;
            $this->resetCreateForm();
            $this->showRequestSuccessModal = true;
            
            // CRITICAL LOG: Verify modal state is set
            Log::info('showRequestSuccessModal SET TO TRUE', [
                'showRequestSuccessModal' => $this->showRequestSuccessModal,
                'showCreateModal' => $this->showCreateModal,
            ]);
            
            // Note: Do NOT call resetPage() here - it can interfere with modal state
        } catch (\Exception $e) {
            Log::error('submitCreateMaintenance EXCEPTION', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->addError('createDescription', 'Error creating maintenance request: ' . $e->getMessage());
            report($e);
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

    /**
     * Get all employees for PIC dropdown
     */
    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'nik', 'position']);
    }

    /**
     * Export maintenance requests to PDF
     */
    public function exportPdf()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('notify', type: 'error', message: 'Only administrators can export reports.');
            return;
        }

        return $this->redirect(route('export.maintenance-request'), navigate: false);
    }

    public function render()
    {
        return view('livewire.maintenance.maintenance-requests-manager', [
            'requests' => $this->requests,
            'availableAssets' => $this->availableAssets,
            'employees' => $this->employees,
        ]);
    }
}
