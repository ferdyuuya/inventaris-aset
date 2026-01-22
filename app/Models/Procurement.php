<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Procurement extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'asset_category_id',
        'location_id',
        'supplier_id',
        'procurement_date',
        'documents',
        'quantity',
        'unit_price',
        'total_cost',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'procurement_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'decimal:0',
        'total_cost' => 'decimal:0',
        'documents' => 'array', // Cast documents JSON to array
    ];

    /**
     * Get the supplier that owns the procurement.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the category for this procurement.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }

    /**
     * Get the location for this procurement.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Get the user who created the procurement.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all assets generated from this procurement.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'procurement_id');
    }
}
