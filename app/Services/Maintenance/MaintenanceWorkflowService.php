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
     * 3. Create asset_maintenances record
     * 4. Update asset status to 'dipelihara'
     * 5. Mark asset as unavailable (is_available = false)
     * 
     * @param MaintenanceRequest $request The request to approve
     * @param User $admin The admin approving the request
     * 
     * @return AssetMaintenance The created maintenance record
     * 
     * @throws Exception If request is not pending or any operation fails
     */
    public function approveRequest(MaintenanceRequest $request, User $admin): AssetMaintenance
    {
        // Validation: Only pending requests can be approved
        if ($request->status !== 'diajukan') {
            throw new Exception(
                "Cannot approve request. Status is '{$request->status}', expected 'diajukan'."
            );
        }

        // Ensure asset is loaded
        if (!$request->asset) {
            $request->load('asset');
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($request, $admin): AssetMaintenance {
                // STEP 1: Update maintenance request status to approved
                $request->update([
                    'status' => 'disetujui',
                    'approved_by' => $admin->id,
                ]);

                // STEP 2: Create asset maintenance record
                $maintenance = AssetMaintenance::create([
                    'asset_id' => $request->asset_id,
                    'maintenance_request_id' => $request->id,
                    'maintenance_date' => now()->toDateString(),
                    'estimated_completion_date' => now()->addDays(7)->toDateString(),
                    'description' => $request->issue_description,
                    'status' => 'dalam_proses',
                    'created_by' => $admin->id,
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
     * 2. Update maintenance record: status → 'selesai', set completed_date
     * 3. Update related maintenance request: status → 'selesai'
     * 4. Update asset:
     *    - status → 'aktif' (back to active)
     *    - is_available → true (available for use)
     *    - condition → 'good' (reset to good condition)
     * 
     * @param AssetMaintenance $maintenance The maintenance to complete
     * @param User $admin The admin completing the maintenance
     * 
     * @return AssetMaintenance The completed maintenance record
     * 
     * @throws Exception If maintenance is not in progress or any operation fails
     */
    public function completeMaintenance(AssetMaintenance $maintenance, User $admin): AssetMaintenance
    {
        // Validation: Only in-progress maintenance can be completed
        if ($maintenance->status !== 'dalam_proses') {
            throw new Exception(
                "Cannot complete maintenance. Status is '{$maintenance->status}', expected 'dalam_proses'."
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
            return DB::transaction(function () use ($maintenance, $admin): AssetMaintenance {
                // STEP 1: Mark maintenance as completed
                $maintenance->update([
                    'completed_date' => now()->toDateString(),
                    'status' => 'selesai',
                ]);

                // STEP 2: Mark related maintenance request as completed
                if ($maintenance->maintenanceRequest) {
                    $maintenance->maintenanceRequest->update([
                        'status' => 'selesai',
                    ]);
                }

                // STEP 3: Restore asset to active state
                if ($maintenance->asset) {
                    $maintenance->asset->update([
                        'status' => 'aktif',           // Back to active (valid status)
                        'is_available' => true,        // Available for use
                        'condition' => 'good',         // Reset to good condition
                    ]);
                }

                return $maintenance;
            });
        } catch (Exception $e) {
            // Re-throw with context for Livewire to handle
            throw new Exception("Failed to complete maintenance: {$e->getMessage()}", 0, $e);
        }
    }
}
