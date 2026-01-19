<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    protected $table = 'asset_maintenances';

    protected $fillable = [
        'asset_id',
        'maintenance_request_id',
        'maintenance_date',
        'estimated_completion_date',
        'completed_date',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'estimated_completion_date' => 'date',
        'completed_date' => 'date',
    ];

    /**
     * Get the asset being maintained.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the maintenance request that triggered this maintenance.
     */
    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class, 'maintenance_request_id');
    }

    /**
     * Get the user who created this maintenance record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope to get active maintenance records.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'dalam_proses');
    }

    /**
     * Scope to get completed maintenance records.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Scope to get overdue maintenance.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'dalam_proses')
            ->where('estimated_completion_date', '<', now()->toDateString());
    }

    /**
     * Check if maintenance is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'dalam_proses' &&
               $this->estimated_completion_date &&
               $this->estimated_completion_date->isPast();
    }

    /**
     * Get days since maintenance started.
     */
    public function getDaysSinceStart(): int
    {
        return now()->diffInDays($this->maintenance_date);
    }

    /**
     * Get estimated duration in days.
     */
    public function getEstimatedDuration(): ?int
    {
        if (!$this->estimated_completion_date) {
            return null;
        }

        return $this->maintenance_date->diffInDays($this->estimated_completion_date);
    }

    /**
     * Get actual duration in days.
     */
    public function getActualDuration(): ?int
    {
        if (!$this->completed_date) {
            return null;
        }

        return $this->maintenance_date->diffInDays($this->completed_date);
    }
}
