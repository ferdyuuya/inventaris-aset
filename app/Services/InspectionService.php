<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Inspection;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

/**
 * InspectionService
 * 
 * Owns all business logic for asset inspection.
 * Handles atomic condition updates and guarantees data consistency.
 * 
 * CORE PRINCIPLES:
 * 1. Inspection = evaluation ONLY
 * 2. Updates asset.condition ONLY
 * 3. Does NOT change asset.status or asset.is_available
 * 4. Does NOT trigger maintenance or disposal
 * 5. All operations are atomic and auditable
 */
class InspectionService
{
    /**
     * Valid condition values
     */
    public const VALID_CONDITIONS = ['baik', 'rusak', 'perlu_perbaikan'];

    /**
     * Create a new inspection for an asset
     * 
     * ATOMIC OPERATION:
     * 1. Validate condition value
     * 2. Create inspection record with condition_before snapshot
     * 3. Update asset.condition to condition_after
     * 
     * @param Asset $asset The asset to inspect
     * @param string $condition The inspection result (condition_after)
     * @param string|null $description Optional inspection notes
     * @param User $admin The admin performing the inspection
     * 
     * @return Inspection The created inspection record
     * 
     * @throws Exception If validation fails or operation errors
     */
    public function createInspection(
        Asset $asset,
        string $condition,
        ?string $description,
        User $admin
    ): Inspection {
        // VALIDATION 1: Condition must be valid
        if (!in_array($condition, self::VALID_CONDITIONS)) {
            throw new Exception(
                "Invalid condition value '{$condition}'. " .
                "Allowed values: " . implode(', ', self::VALID_CONDITIONS)
            );
        }

        // VALIDATION 2: Asset must not be disposed
        if ($asset->isDisposed()) {
            throw new Exception(
                "Cannot inspect disposed asset '{$asset->asset_code}'."
            );
        }

        // Atomic transaction: All operations must succeed or all rollback
        try {
            return DB::transaction(function () use ($asset, $condition, $description, $admin): Inspection {
                $now = now();

                // Snapshot current condition before update
                $conditionBefore = $asset->condition;

                // STEP 1: Create inspection record
                $inspection = Inspection::create([
                    'asset_id' => $asset->id,
                    'condition_before' => $conditionBefore,
                    'condition_after' => $condition,
                    'description' => $description ? trim($description) : null,
                    'inspected_by' => $admin->id,
                    'inspected_at' => $now,
                ]);

                // STEP 2: Update asset condition ONLY
                // Note: Does NOT change status or is_available
                $asset->update([
                    'condition' => $condition,
                ]);

                return $inspection;
            });
        } catch (Exception $e) {
            throw new Exception("Failed to create inspection: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get inspection history for an asset
     * 
     * @param Asset $asset
     * @param int $limit Maximum number of records to return
     * @return Collection
     */
    public function getInspectionHistory(Asset $asset, int $limit = 10): Collection
    {
        return Inspection::forAsset($asset->id)
            ->with('inspector:id,name')
            ->latestFirst()
            ->limit($limit)
            ->get();
    }

    /**
     * Get all inspections with pagination
     * 
     * @param string|null $search Search by asset code or name
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllInspections(?string $search = null, int $perPage = 25)
    {
        return Inspection::query()
            ->with([
                'asset:id,asset_code,name',
                'inspector:id,name',
            ])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('asset', function ($q) use ($search) {
                    $q->where('asset_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->latestFirst()
            ->paginate($perPage);
    }

    /**
     * Get a single inspection with full details
     * 
     * @param int $inspectionId
     * @return Inspection|null
     */
    public function getInspectionDetail(int $inspectionId): ?Inspection
    {
        return Inspection::with(['asset', 'inspector'])
            ->find($inspectionId);
    }

    /**
     * Delete an inspection record
     * 
     * Note: This does NOT revert the asset condition change.
     * The condition change is historical and should remain.
     * 
     * @param Inspection $inspection
     * @return bool
     */
    public function deleteInspection(Inspection $inspection): bool
    {
        return $inspection->delete();
    }
}
