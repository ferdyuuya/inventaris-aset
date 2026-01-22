<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nik',
        'name',
        'gender',
        'phone',
        'position'
    ];

    protected $casts = [
        'user_id' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all asset loans for this employee.
     */
    public function assetLoans(): HasMany
    {
        return $this->hasMany(AssetLoan::class, 'borrower_employee_id');
    }

    /**
     * Get active (currently borrowed) asset loans.
     */
    public function activeLoans(): HasMany
    {
        return $this->assetLoans()->where('status', AssetLoan::STATUS_DIPINJAM);
    }

    /**
     * Get currently borrowed assets.
     */
    public function borrowedAssets()
    {
        return $this->hasManyThrough(
            Asset::class,
            AssetLoan::class,
            'borrower_employee_id', // Foreign key on AssetLoan
            'id',                    // Foreign key on Asset
            'id',                    // Local key on Employee
            'asset_id'               // Local key on AssetLoan
        )->where('asset_loans.status', AssetLoan::STATUS_DIPINJAM);
    }

    public static function getGenderOptions(): array
    {
        return [
            'Laki-Laki' => 'Laki-Laki',
            'Perempuan' => 'Perempuan'
        ];
    }
}
