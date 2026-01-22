<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'asset_code',
        'name',
        'category_id',
        'location_id',
        'purchase_date',
        'purchase_price',
        'invoice_number',
        'supplier_id',
        'procurement_id',
        'condition',
        'is_available',
        'status',
        'disposed_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'purchase_price' => 'decimal:0',
        'is_available' => 'boolean',
        'disposed_at' => 'datetime',
    ];

    /**
     * Get the asset category associated with this asset.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the location associated with this asset.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    /**
     * Get the supplier associated with this asset.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the procurement record that generated this asset.
     */
    public function procurement(): BelongsTo
    {
        return $this->belongsTo(Procurement::class, 'procurement_id');
    }

    /**
     * Get all transactions for this asset.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(AssetTransaction::class, 'asset_id');
    }

    /**
     * Get all loans for this asset.
     */
    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class, 'asset_id');
    }

    /**
     * Get all maintenance records for this asset.
     */
    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    /**
     * Get all inspection records for this asset.
     */
    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'asset_id');
    }

    /**
     * Get the latest inspection for this asset.
     */
    public function latestInspection()
    {
        return $this->hasOne(Inspection::class, 'asset_id')->latestOfMany('inspected_at');
    }

    /**
     * Get the disposal record for this asset (if disposed).
     */
    public function disposal()
    {
        return $this->hasOne(AssetDisposal::class, 'asset_id');
    }

    /**
     * Check if asset is disposed.
     */
    public function isDisposed(): bool
    {
        return $this->status === 'dihapuskan';
    }

    /**
     * Check if asset is currently borrowed.
     */
    public function isBorrowed(): bool
    {
        return $this->status === 'dipinjam';
    }

    /**
     * Check if asset is under active maintenance.
     */
    public function isUnderMaintenance(): bool
    {
        return $this->status === 'dipelihara';
    }

    /**
     * Check if asset can be disposed.
     * Asset can be disposed only if:
     * - Not already disposed
     * - Not currently borrowed
     * - Not under active maintenance
     */
    public function canBeDisposed(): bool
    {
        return !$this->isDisposed() 
            && !$this->isBorrowed() 
            && !$this->isUnderMaintenance();
    }

    /**
     * Get the current active loan if any.
     */
    public function activeLoan()
    {
        return $this->loans()
            ->where('status', 'dipinjam')
            ->latest('loan_date')
            ->first();
    }

    /**
     * Get the current active maintenance if any.
     */
    public function activeMaintenance()
    {
        return $this->maintenances()
            ->whereNull('completed_date')
            ->latest('maintenance_date')
            ->first();
    }

    /**
     * Scope to get only available assets.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'aktif')
            ->where('is_available', true);
    }

    /**
     * Scope to get active assets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'nonaktif')
            ->where('status', '!=', 'dihapuskan');
    }

    /**
     * Scope to exclude disposed assets (default for most queries).
     */
    public function scopeNotDisposed($query)
    {
        return $query->where('status', '!=', 'dihapuskan');
    }

    /**
     * Scope to get only disposed assets.
     */
    public function scopeDisposed($query)
    {
        return $query->where('status', 'dihapuskan');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by location.
     */
    public function scopeByLocation($query, $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to search by code or name.
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where('asset_code', 'like', "%{$searchTerm}%")
            ->orWhere('name', 'like', "%{$searchTerm}%");
    }
}
