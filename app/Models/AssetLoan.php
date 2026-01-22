<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLoan extends Model
{
    /**
     * Status constants for asset loans
     */
    public const STATUS_DIPINJAM = 'dipinjam';
    public const STATUS_SELESAI = 'selesai';

    /**
     * Condition options after return
     */
    public const CONDITION_GOOD = 'baik';
    public const CONDITION_DAMAGED = 'rusak';

    protected $fillable = [
        'asset_id',
        'borrower_employee_id',
        'loan_date',
        'expected_return_date',
        'return_date',
        'condition_after_return',
        'notes',
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
     * Scope to get active loans (status = dipinjam).
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_DIPINJAM);
    }

    /**
     * Scope to get completed loans (status = selesai).
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_SELESAI);
    }

    /**
     * Scope to get overdue loans.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_DIPINJAM)
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now()->toDateString());
    }

    /**
     * Check if loan is active (currently borrowed).
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_DIPINJAM;
    }

    /**
     * Check if loan is completed (returned).
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_SELESAI;
    }

    /**
     * Check if loan is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_DIPINJAM &&
               $this->expected_return_date &&
               $this->expected_return_date->isPast();
    }

    /**
     * Get days until expected return.
     * Returns negative number if overdue.
     */
    public function getDaysUntilReturn(): ?int
    {
        if ($this->status !== self::STATUS_DIPINJAM || !$this->expected_return_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->expected_return_date, false);
    }

    /**
     * Get loan duration in days.
     * For active loans, calculates from loan_date to now.
     * For completed loans, calculates from loan_date to return_date.
     */
    public function getDurationDays(): int
    {
        $endDate = $this->return_date ?? now();
        return $this->loan_date->diffInDays($endDate);
    }

    /**
     * Get available condition options for return.
     */
    public static function getConditionOptions(): array
    {
        return [
            self::CONDITION_GOOD => 'Baik (Good)',
            self::CONDITION_DAMAGED => 'Rusak (Damaged)',
        ];
    }

    /**
     * Get available status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DIPINJAM => 'Dipinjam (Borrowed)',
            self::STATUS_SELESAI => 'Selesai (Completed)',
        ];
    }
}
