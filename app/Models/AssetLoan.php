<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    protected $fillable = [
        'asset_id',
        'borrower_employee_id',
        'loan_date',
        'expected_return_date',
        'return_date',
        'status',
    ];

    protected $casts = [
        'loan_date' => 'date',
        'expected_return_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Get the asset being loaned.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    /**
     * Get the borrower employee.
     */
    public function borrower(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'borrower_employee_id');
    }

    /**
     * Scope to get active loans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'dipinjam');
    }

    /**
     * Scope to get overdue loans.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'dipinjam')
            ->where('expected_return_date', '<', now()->toDateString());
    }

    /**
     * Check if loan is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'dipinjam' &&
               $this->expected_return_date &&
               $this->expected_return_date->isPast();
    }

    /**
     * Get days until expected return.
     */
    public function getDaysUntilReturn(): ?int
    {
        if ($this->status !== 'dipinjam' || !$this->expected_return_date) {
            return null;
        }

        return now()->diffInDays($this->expected_return_date, false);
    }
}
