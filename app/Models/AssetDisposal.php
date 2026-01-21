<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AssetDisposal Model
 * 
 * Represents an audit record of asset disposal.
 * Each disposal is FINAL and IRREVERSIBLE.
 * 
 * Purpose:
 * - Preserve disposal history and accountability
 * - Track who disposed which asset and why
 * - Provide audit trail for compliance
 */
class AssetDisposal extends Model
{
    protected $table = 'asset_disposals';

    protected $fillable = [
        'asset_id',
        'disposed_by',
        'reason',
        'disposed_at',
    ];

    protected $casts = [
        'disposed_at' => 'datetime',
    ];

    /**
     * Get the asset that was disposed.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the admin who disposed the asset.
     */
    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }
}
