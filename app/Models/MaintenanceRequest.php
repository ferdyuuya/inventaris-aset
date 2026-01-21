<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';

    protected $fillable = [
        'asset_id',
        'requested_by',
        'request_date',
        'issue_description',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'request_date' => 'date',
    ];

    /**
     * Get the asset being requested for maintenance.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the user who requested the maintenance.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved this request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the auto-generated maintenance record (if approved).
     */
    public function maintenance(): HasOne
    {
        return $this->hasOne(AssetMaintenance::class, 'maintenance_request_id');
    }

    /**
     * Scope to get pending requests (not yet approved or rejected).
     */
    public function scopePending($query)
    {
        return $query->where('status', 'diajukan');
    }

    /**
     * Scope to get approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'disetujui');
    }

    /**
     * Scope to get completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'selesai');
    }

    /**
     * Scope to get rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'ditolak');
    }

    /**
     * Custom scope for default ordering: by status priority, then by newest first.
     */
    public function scopeOrderedByStatus($query)
    {
        return $query->orderByRaw("
            CASE
                WHEN status = 'diajukan' THEN 1
                WHEN status = 'disetujui' THEN 2
                WHEN status = 'selesai' THEN 3
                WHEN status = 'ditolak' THEN 4
                ELSE 5
            END
        ")->orderBy('created_at', 'desc');
    }
}
