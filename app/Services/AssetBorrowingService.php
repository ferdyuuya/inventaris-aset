<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssetBorrowingService
{
    /**
     * Borrow an asset
     */
    public function borrowAsset(Asset $asset, int $employeeId, ?string $expectedReturnDate = null): AssetLoan
    {
        // Validate the employee exists
        $employee = Employee::findOrFail($employeeId);

        // Check if asset can be borrowed
        if (!app(AssetService::class)->canPerformAction($asset, 'borrow')) {
            throw new \InvalidArgumentException('Asset cannot be borrowed in its current state');
        }

        // Check if there's already an active loan
        if ($this->hasActiveLoan($asset)) {
            throw new \InvalidArgumentException('Asset is already borrowed');
        }

        // Validate expected return date
        if ($expectedReturnDate) {
            $returnDate = Carbon::parse($expectedReturnDate);
            if ($returnDate->isPast()) {
                throw new \InvalidArgumentException('Expected return date cannot be in the past');
            }
        }

        // Create loan record
        $loan = AssetLoan::create([
            'asset_id' => $asset->id,
            'borrower_employee_id' => $employeeId,
            'loan_date' => now()->toDateString(),
            'expected_return_date' => $expectedReturnDate,
            'status' => 'dipinjam',
        ]);

        // Update asset status
        $asset->update([
            'status' => 'dipinjam',
            'is_available' => false,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $loan;
    }

    /**
     * Return a borrowed asset
     */
    public function returnAsset(int $loanId): AssetLoan
    {
        $loan = AssetLoan::findOrFail($loanId);

        // Validate loan is still active
        if ($loan->status !== 'dipinjam') {
            throw new \InvalidArgumentException('Loan is not active');
        }

        // Update loan record
        $loan->update([
            'return_date' => now()->toDateString(),
            'status' => 'dikembalikan',
        ]);

        // Update asset status
        $asset = $loan->asset;
        $asset->update([
            'status' => 'aktif',
            'is_available' => true,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $loan;
    }

    /**
     * Mark a loan as lost
     */
    public function markAsLost(int $loanId): AssetLoan
    {
        $loan = AssetLoan::findOrFail($loanId);

        // Only active loans can be marked as lost
        if ($loan->status !== 'dipinjam') {
            throw new \InvalidArgumentException('Only active loans can be marked as lost');
        }

        // Update loan
        $loan->update([
            'return_date' => now()->toDateString(),
            'status' => 'hilang',
        ]);

        // Update asset - mark as nonaktif since it's lost
        $asset = $loan->asset;
        $asset->update([
            'status' => 'nonaktif',
            'is_available' => false,
        ]);

        // Invalidate cache
        app(AssetService::class)->invalidateSummaryCache();

        return $loan;
    }

    /**
     * Get current borrower for an asset
     */
    public function getCurrentBorrower(Asset $asset): ?AssetLoan
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->where('status', 'dipinjam')
            ->with('borrower')
            ->first();
    }

    /**
     * Check if asset has an active loan
     */
    public function hasActiveLoan(Asset $asset): bool
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->where('status', 'dipinjam')
            ->exists();
    }

    /**
     * Get borrowing history
     */
    public function getBorrowingHistory(Asset $asset)
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->with('borrower.user')
            ->orderBy('loan_date', 'desc')
            ->paginate(10);
    }

    /**
     * Get overdue borrowings
     */
    public function getOverdueBorrowings()
    {
        return AssetLoan::where('status', 'dipinjam')
            ->where('expected_return_date', '<', now()->toDateString())
            ->with(['asset', 'borrower.user'])
            ->orderBy('expected_return_date', 'asc')
            ->get();
    }

    /**
     * Get borrowing summary for an employee
     */
    public function getEmployeeBorrowingSummary(int $employeeId): array
    {
        $activeBorrows = AssetLoan::where('borrower_employee_id', $employeeId)
            ->where('status', 'dipinjam')
            ->count();

        $totalBorrows = AssetLoan::where('borrower_employee_id', $employeeId)->count();

        $overdue = AssetLoan::where('borrower_employee_id', $employeeId)
            ->where('status', 'dipinjam')
            ->where('expected_return_date', '<', now()->toDateString())
            ->count();

        return [
            'active_borrows' => $activeBorrows,
            'total_borrows' => $totalBorrows,
            'overdue' => $overdue,
        ];
    }
}
