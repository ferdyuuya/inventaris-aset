<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

/**
 * AssetLoanService - Service Layer for Asset Borrowing
 * 
 * This service handles all business logic for asset borrowing operations.
 * All state transitions are atomic and wrapped in database transactions.
 * 
 * DESIGN PRINCIPLES:
 * 1. Borrowing is ADMIN-CONTROLLED
 * 2. No request/approval states
 * 3. One asset per loan
 * 4. Borrowing affects availability, not lifecycle
 * 5. Borrowing is reversible
 * 
 * WORKFLOW:
 * 1. createLoan() - Admin creates a loan, asset becomes unavailable
 * 2. finishLoan() - Admin completes the loan, asset becomes available again
 */
class AssetLoanService
{
    /**
     * Create a new asset loan (borrow asset)
     * 
     * Preconditions:
     * - Asset must be available (is_available = true)
     * - Asset must not be disposed, under maintenance
     * - No active loan for this asset
     * 
     * Effects (atomic):
     * - Creates AssetLoan record with status = dipinjam
     * - Updates Asset: status = dipinjam, is_available = false
     * 
     * @param Asset $asset The asset to borrow
     * @param Employee $employee The employee borrowing the asset
     * @param array $data Loan data [borrow_date, expected_return_date?, notes?]
     * @return AssetLoan The created loan record
     * @throws InvalidArgumentException If asset cannot be borrowed
     */
    public function createLoan(Asset $asset, Employee $employee, array $data): AssetLoan
    {
        // Validate asset can be borrowed
        $this->validateCanBorrow($asset);

        // Validate borrow date
        $borrowDate = $data['borrow_date'] ?? now()->toDateString();
        
        // Validate expected return date if provided
        if (!empty($data['expected_return_date'])) {
            $expectedReturnDate = \Carbon\Carbon::parse($data['expected_return_date']);
            $borrowDateParsed = \Carbon\Carbon::parse($borrowDate);
            
            if ($expectedReturnDate->lt($borrowDateParsed)) {
                throw new InvalidArgumentException('Expected return date cannot be before borrow date.');
            }
        }

        return DB::transaction(function () use ($asset, $employee, $data, $borrowDate) {
            // Create loan record
            $loan = AssetLoan::create([
                'asset_id' => $asset->id,
                'borrower_employee_id' => $employee->id,
                'loan_date' => $borrowDate,
                'expected_return_date' => $data['expected_return_date'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => AssetLoan::STATUS_DIPINJAM,
            ]);

            // Update asset status
            $asset->update([
                'status' => 'dipinjam',
                'is_available' => false,
            ]);

            // Invalidate cache
            $this->invalidateCache();

            Log::info('Asset loan created', [
                'loan_id' => $loan->id,
                'asset_id' => $asset->id,
                'employee_id' => $employee->id,
                'borrow_date' => $borrowDate,
            ]);

            return $loan;
        });
    }

    /**
     * Finish an asset loan (return asset)
     * 
     * Preconditions:
     * - Loan must be active (status = dipinjam)
     * 
     * Effects (atomic):
     * - Updates AssetLoan: status = selesai, return_date, condition_after_return
     * - Updates Asset: status = aktif, is_available = true, condition = condition_after_return
     * 
     * @param AssetLoan $loan The loan to finish
     * @param array $data Return data [return_date, condition_after_return, notes?]
     * @return AssetLoan The updated loan record
     * @throws InvalidArgumentException If loan cannot be finished
     */
    public function finishLoan(AssetLoan $loan, array $data): AssetLoan
    {
        // Validate loan is active
        if (!$loan->isActive()) {
            throw new InvalidArgumentException('This loan is not active. Only active loans can be finished.');
        }

        // Validate return date
        $returnDate = $data['return_date'] ?? now()->toDateString();
        $returnDateParsed = \Carbon\Carbon::parse($returnDate);
        
        if ($returnDateParsed->lt($loan->loan_date)) {
            throw new InvalidArgumentException('Return date cannot be before the borrow date.');
        }

        // Validate condition after return
        $validConditions = [AssetLoan::CONDITION_GOOD, AssetLoan::CONDITION_DAMAGED];
        $conditionAfterReturn = $data['condition_after_return'] ?? AssetLoan::CONDITION_GOOD;
        
        if (!in_array($conditionAfterReturn, $validConditions)) {
            throw new InvalidArgumentException('Invalid condition after return. Must be: baik or rusak.');
        }

        return DB::transaction(function () use ($loan, $data, $returnDate, $conditionAfterReturn) {
            // Update loan record
            $loan->update([
                'status' => AssetLoan::STATUS_SELESAI,
                'return_date' => $returnDate,
                'condition_after_return' => $conditionAfterReturn,
                'notes' => $data['notes'] ?? $loan->notes,
            ]);

            // Update asset status and condition
            $asset = $loan->asset;
            $asset->update([
                'status' => 'aktif',
                'is_available' => true,
                'condition' => $conditionAfterReturn,
            ]);

            // Invalidate cache
            $this->invalidateCache();

            Log::info('Asset loan finished', [
                'loan_id' => $loan->id,
                'asset_id' => $asset->id,
                'return_date' => $returnDate,
                'condition_after_return' => $conditionAfterReturn,
            ]);

            return $loan->fresh();
        });
    }

    /**
     * Validate that an asset can be borrowed
     * 
     * @param Asset $asset
     * @throws InvalidArgumentException
     */
    public function validateCanBorrow(Asset $asset): void
    {
        // Check if asset is available
        if (!$asset->is_available) {
            throw new InvalidArgumentException('This asset is not available for borrowing.');
        }

        // Check if asset is not disposed
        if ($asset->isDisposed()) {
            throw new InvalidArgumentException('Cannot borrow a disposed asset.');
        }

        // Check if asset is not under maintenance
        if ($asset->isUnderMaintenance()) {
            throw new InvalidArgumentException('Cannot borrow an asset that is under maintenance.');
        }

        // Check if there's already an active loan
        if ($this->hasActiveLoan($asset)) {
            throw new InvalidArgumentException('This asset already has an active loan.');
        }
    }

    /**
     * Check if asset can be borrowed (for UI visibility)
     */
    public function canBorrow(Asset $asset): bool
    {
        try {
            $this->validateCanBorrow($asset);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    /**
     * Check if asset has an active loan
     */
    public function hasActiveLoan(Asset $asset): bool
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->where('status', AssetLoan::STATUS_DIPINJAM)
            ->exists();
    }

    /**
     * Get current active loan for an asset
     */
    public function getActiveLoan(Asset $asset): ?AssetLoan
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->where('status', AssetLoan::STATUS_DIPINJAM)
            ->with('borrower')
            ->first();
    }

    /**
     * Get borrowing history for an asset
     */
    public function getBorrowingHistory(Asset $asset, int $perPage = 10)
    {
        return AssetLoan::where('asset_id', $asset->id)
            ->with('borrower')
            ->orderBy('loan_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get borrowing history for an employee
     */
    public function getEmployeeBorrowingHistory(Employee $employee, int $perPage = 10)
    {
        return AssetLoan::where('borrower_employee_id', $employee->id)
            ->with('asset.category', 'asset.location')
            ->orderBy('loan_date', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get currently borrowed assets by an employee
     */
    public function getEmployeeActiveBorrowings(Employee $employee)
    {
        return AssetLoan::where('borrower_employee_id', $employee->id)
            ->where('status', AssetLoan::STATUS_DIPINJAM)
            ->with('asset.category', 'asset.location')
            ->orderBy('loan_date', 'desc')
            ->get();
    }

    /**
     * Get all active loans (currently borrowed assets)
     */
    public function getActiveLoans()
    {
        return AssetLoan::where('status', AssetLoan::STATUS_DIPINJAM)
            ->with(['asset.category', 'asset.location', 'borrower'])
            ->orderBy('loan_date', 'desc')
            ->get();
    }

    /**
     * Get overdue loans
     */
    public function getOverdueLoans()
    {
        return AssetLoan::where('status', AssetLoan::STATUS_DIPINJAM)
            ->whereNotNull('expected_return_date')
            ->where('expected_return_date', '<', now()->toDateString())
            ->with(['asset.category', 'asset.location', 'borrower'])
            ->orderBy('expected_return_date', 'asc')
            ->get();
    }

    /**
     * Get loan summary metrics
     */
    public function getSummaryMetrics(): array
    {
        return [
            'total_loans' => AssetLoan::count(),
            'active_loans' => AssetLoan::active()->count(),
            'completed_loans' => AssetLoan::completed()->count(),
            'overdue_loans' => AssetLoan::overdue()->count(),
        ];
    }

    /**
     * Get employee borrowing summary
     */
    public function getEmployeeBorrowingSummary(Employee $employee): array
    {
        return [
            'active_loans' => AssetLoan::where('borrower_employee_id', $employee->id)
                ->active()
                ->count(),
            'total_loans' => AssetLoan::where('borrower_employee_id', $employee->id)
                ->count(),
            'overdue_loans' => AssetLoan::where('borrower_employee_id', $employee->id)
                ->overdue()
                ->count(),
        ];
    }

    /**
     * Invalidate related caches
     */
    protected function invalidateCache(): void
    {
        cache()->forget('asset.summary');
        cache()->forget('loan.summary');
    }
}
