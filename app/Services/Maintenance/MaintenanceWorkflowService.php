<?php

namespace App\Services\Maintenance;

use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * MaintenanceWorkflowService
 * 
 * Owns all business logic for maintenance workflow.
 * Handles atomic state transitions and guarantees data consistency.
 * 
 * SINGLE RESPONSIBILITY: Maintenance request approval and completion workflows
 * 
 * All operations are transactional:
 * - If any step fails, entire transaction rolls back
 * - No partial updates or corrupted states
 * - Exceptions thrown on validation or database errors
 */
class MaintenanceWorkflowService
{
    /**
     * Approve a pending maintenance request
     * 
     * ATOMIC OPERATION:
     * 1. Validate request is in 'diajukan' status
     * 2. Update request status to 'disetujui'
     * 3. Create asset_maintenances record with assigned PIC employee
     * 4. Update asset status to 'dipelihara'
     * 5. Mark asset as unavailable (is_available = false)
     * 
     * @param MaintenanceRequest $request The request to approve
     * @param User $admin The admin approving the request
     * @param int|null $picEmployeeId The employee ID of the Person In Charge for maintenance execution
     * 
     * @return AssetMaintenance The created maintenance record
     * 
     * @throws Exception If request is not pending, PIC not provided, or any operation fails
     */
    public function approveRequest(MaintenanceRequest $request, User $admin, ?int $picEmployeeId = null): AssetMaintenance
    {
        // Validation: Only pending requests can be approved
        if ($request->status !== 'diajukan') {
            throw new Exception(
                "Cannot approve request. Status is '{$request->status}', expected 'diajukan'."
            );
        }

        // Validation: PIC employee is required
        if (empty($picEmployeeId)) {
            throw new Exception("PIC (Person In Charge) employee must be assigned when approving a maintenance request.");
        }

        // Ensure asset is loaded
        if (!$request->asset) {
            $request->load('asset');
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($request, $admin, $picEmployeeId): AssetMaintenance {
                // STEP 1: Update maintenance request status to approved
                $request->update([
                    'status' => 'disetujui',
                    'approved_by' => $admin->id,
                ]);

                // STEP 2: Create asset maintenance record with PIC employee
                $maintenance = AssetMaintenance::create([
                    'asset_id' => $request->asset_id,
                    'maintenance_request_id' => $request->id,
                    'maintenance_date' => now()->toDateString(),
                    'estimated_completion_date' => now()->addDays(7)->toDateString(),
                    'description' => $request->issue_description,
                    'status' => 'dalam_proses',
                    'created_by' => $admin->id,
                    'pic_employee_id' => $picEmployeeId,
                ]);

                // STEP 3: Mark asset as under maintenance
                $request->asset->update([
                    'status' => 'dipelihara',
                    'is_available' => false,
                ]);

                return $maintenance;
            });
        } catch (Exception $e) {
            // Re-throw with context for Livewire to handle
            throw new Exception("Failed to approve maintenance request: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Complete an in-progress maintenance
     * 
     * ATOMIC OPERATION:
     * 1. Validate maintenance is in 'dalam_proses' status
     * 2. Update asset_maintenances: result, feedback, status → 'selesai', completed_date
     * 3. Update maintenance_requests: result, feedback, status → 'selesai'
     * 4. Update asset:
     *    - status → 'aktif' (back to active)
     *    - is_available → true (available for use)
     *    - condition → based on result ('baik' if result is 'baik', 'rusak' if result is 'rusak')
     * 
     * @param AssetMaintenance $maintenance The maintenance to complete
     * @param User $admin The admin completing the maintenance
     * @param string $result The maintenance result: 'baik' or 'rusak'
     * @param string $feedback Technical explanation of work done (required)
     * 
     * @return AssetMaintenance The completed maintenance record
     * 
     * @throws Exception If maintenance is not in progress or any operation fails
     */
    public function completeMaintenance(
        AssetMaintenance $maintenance, 
        User $admin, 
        string $result, 
        string $feedback
    ): AssetMaintenance {
        // Validation: Only in-progress maintenance can be completed
        if ($maintenance->status !== 'dalam_proses') {
            throw new Exception(
                "Cannot complete maintenance. Status is '{$maintenance->status}', expected 'dalam_proses'."
            );
        }

        // Validation: Result must be valid
        if (!in_array($result, ['baik', 'rusak'])) {
            throw new Exception(
                "Invalid result value. Expected 'baik' or 'rusak', got '{$result}'."
            );
        }

        // Validation: Feedback is required
        if (empty(trim($feedback))) {
            throw new Exception("Feedback is required when completing maintenance.");
        }

        // Ensure relationships are loaded
        if (!$maintenance->asset) {
            $maintenance->load('asset');
        }
        if (!$maintenance->maintenanceRequest) {
            $maintenance->load('maintenanceRequest');
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($maintenance, $admin, $result, $feedback): AssetMaintenance {
                // STEP 1: Mark maintenance as completed with result and feedback
                $maintenance->update([
                    'result' => $result,
                    'feedback' => $feedback,
                    'completed_date' => now()->toDateString(),
                    'status' => 'selesai',
                ]);

                // STEP 2: Propagate result and feedback to maintenance request
                if ($maintenance->maintenanceRequest) {
                    $maintenance->maintenanceRequest->update([
                        'result' => $result,
                        'feedback' => $feedback,
                        'status' => 'selesai',
                    ]);
                }

                // STEP 3: Restore asset to active state with condition based on result
                if ($maintenance->asset) {
                    $maintenance->asset->update([
                        'status' => 'aktif',           // Back to active
                        'is_available' => true,        // Available for use
                        'condition' => $result,        // Set condition based on maintenance result
                    ]);
                }

                return $maintenance;
            });
        } catch (Exception $e) {
            // Re-throw with context for Livewire to handle
            throw new Exception("Failed to complete maintenance: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Cancel an in-progress maintenance
     * 
     * ATOMIC OPERATION:
     * 1. Validate maintenance is NOT already completed or cancelled
     * 2. Update maintenance record: status → 'dibatalkan'
     * 3. Update related maintenance request: status → 'dibatalkan'
     * 4. Update asset:
     *    - status → 'aktif' (back to active)
     *    - is_available → true (available for use)
     *    - condition → unchanged (do NOT reset condition on cancellation)
     * 
     * @param AssetMaintenance $maintenance The maintenance to cancel
     * @param User $admin The admin cancelling the maintenance
     * @param string|null $reason Optional reason for cancellation
     * 
     * @return AssetMaintenance The cancelled maintenance record
     * 
     * @throws Exception If maintenance is already completed/cancelled or any operation fails
     */
    public function cancelMaintenance(AssetMaintenance $maintenance, User $admin, ?string $reason = null): AssetMaintenance
    {
        // Validation: Only in-progress maintenance can be cancelled
        if ($maintenance->status === 'selesai') {
            throw new Exception(
                "Cannot cancel maintenance. Status is 'selesai' (completed). Completed maintenance cannot be cancelled."
            );
        }

        if ($maintenance->status === 'dibatalkan') {
            throw new Exception(
                "Cannot cancel maintenance. Status is already 'dibatalkan' (cancelled)."
            );
        }

        // Ensure relationships are loaded
        if (!$maintenance->asset) {
            $maintenance->load('asset');
        }
        if (!$maintenance->maintenanceRequest) {
            $maintenance->load('maintenanceRequest');
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($maintenance, $admin, $reason): AssetMaintenance {
                // STEP 1: Mark maintenance as cancelled
                $updateData = [
                    'status' => 'dibatalkan',
                ];
                
                // If reason provided and description column can hold it, append to description
                if ($reason) {
                    $updateData['description'] = $maintenance->description 
                        ? $maintenance->description . "\n\n[CANCELLED] " . $reason
                        : "[CANCELLED] " . $reason;
                }
                
                $maintenance->update($updateData);

                // STEP 2: Mark related maintenance request as rejected/cancelled
                // Note: maintenance_requests uses 'ditolak' (rejected) as the cancelled status
                if ($maintenance->maintenanceRequest) {
                    $maintenance->maintenanceRequest->update([
                        'status' => 'ditolak',
                    ]);
                }

                // STEP 3: Restore asset to active state WITHOUT changing condition
                if ($maintenance->asset) {
                    $maintenance->asset->update([
                        'status' => 'aktif',           // Back to active
                        'is_available' => true,        // Available for use
                        // condition is NOT changed - remains as-is
                    ]);
                }

                return $maintenance;
            });
        } catch (Exception $e) {
            // Re-throw with context for Livewire to handle
            throw new Exception("Failed to cancel maintenance: {$e->getMessage()}", 0, $e);
        }
    }
}
