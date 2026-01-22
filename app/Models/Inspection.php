<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Inspection Model
 * 
 * Represents a single-asset inspection record.
 * Each inspection evaluates and updates the condition of ONE asset.
 * 
 * Purpose:
 * - Record inspection results
 * - Track condition changes over time
 * - Provide audit trail for asset evaluations
 * 
 * IMPORTANT:
 * - Inspection updates asset.condition ONLY
 * - Does NOT change asset.status or asset.is_available
 * - Does NOT trigger maintenance or disposal
 */
class Inspection extends Model
{
    protected $table = 'inspections';

    protected $fillable = [
        'asset_id',
        'condition_before',
        'condition_after',
        'description',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    /**
     * Get the asset that was inspected.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the user who performed the inspection.
     */
    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Get human-readable condition label.
     */
    public function getConditionAfterLabelAttribute(): string
    {
        return match($this->condition_after) {
            'baik' => 'Good',
            'rusak' => 'Damaged',
            'perlu_perbaikan' => 'Needs Repair',
            default => 'Unknown',
        };
    }

    /**
     * Get condition badge color for UI.
     */
    public function getConditionColorAttribute(): string
    {
        return match($this->condition_after) {
            'baik' => 'green',
            'rusak' => 'red',
            'perlu_perbaikan' => 'yellow',
            default => 'zinc',
        };
    }

    /**
     * Scope to get inspections for a specific asset.
     */
    public function scopeForAsset($query, $assetId)
    {
        return $query->where('asset_id', $assetId);
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatestFirst($query)
    {
        return $query->orderBy('inspected_at', 'desc');
    }
}
