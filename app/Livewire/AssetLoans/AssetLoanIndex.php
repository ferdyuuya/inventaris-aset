<?php

namespace App\Livewire\AssetLoans;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Services\AssetLoanService;

class AssetLoanIndex extends Component
{
    use WithPagination;

    // Search and filters
    public string $search = '';
    public string $statusFilter = '';
    public string $overdueFilter = '';
    
    // Modals
    public bool $showCreateModal = false;
    public bool $showReturnModal = false;
    
    // Create loan form
    public ?int $selectedAssetId = null;
    public ?int $selectedEmployeeId = null;
    public ?string $borrowDate = null;
    public ?string $expectedReturnDate = null;
    public string $borrowNotes = '';
    
    // Return loan form
    public ?int $returnLoanId = null;
    public ?string $returnDate = null;
    public string $returnCondition = 'baik';
    public string $returnNotes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'overdueFilter' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingOverdueFilter()
    {
        $this->resetPage();
    }

    /**
     * Get paginated loans with filters
     */
    #[Computed]
    public function loans()
    {
        $query = AssetLoan::query()
            ->with(['asset.category', 'asset.location', 'borrower'])
            ->orderBy('loan_date', 'desc');

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('asset', function ($q2) {
                    $q2->where('asset_code', 'like', "%{$this->search}%")
                       ->orWhere('name', 'like', "%{$this->search}%");
                })
                ->orWhereHas('borrower', function ($q2) {
                    $q2->where('name', 'like', "%{$this->search}%");
                });
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply overdue filter
        if ($this->overdueFilter === 'overdue') {
            $query->overdue();
        }

        return $query->paginate(15);
    }

    /**
     * Get available assets for borrowing
     */
    #[Computed]
    public function availableAssets()
    {
        return Asset::where('is_available', true)
            ->where('status', '!=', 'dihapuskan')
            ->where('status', '!=', 'dipelihara')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all employees
     */
    #[Computed]
    public function employees()
    {
        return Employee::orderBy('name')->get();
    }

    /**
     * Get loan summary metrics
     */
    #[Computed]
    public function summaryMetrics()
    {
        return app(AssetLoanService::class)->getSummaryMetrics();
    }

    /**
     * Open create loan modal
     */
    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->borrowDate = now()->toDateString();
        $this->showCreateModal = true;
    }

    /**
     * Close create loan modal
     */
    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    /**
     * Reset create form
     */
    public function resetCreateForm(): void
    {
        $this->selectedAssetId = null;
        $this->selectedEmployeeId = null;
        $this->borrowDate = null;
        $this->expectedReturnDate = null;
        $this->borrowNotes = '';
        $this->resetValidation();
    }

    /**
     * Submit create loan
     */
    public function submitCreate(): void
    {
        $this->validate([
            'selectedAssetId' => 'required|exists:assets,id',
            'selectedEmployeeId' => 'required|exists:employees,id',
            'borrowDate' => 'required|date',
            'expectedReturnDate' => 'nullable|date|after_or_equal:borrowDate',
            'borrowNotes' => 'nullable|string|max:1000',
        ]);

        try {
            $asset = Asset::findOrFail($this->selectedAssetId);
            $employee = Employee::findOrFail($this->selectedEmployeeId);

            app(AssetLoanService::class)->createLoan(
                $asset,
                $employee,
                [
                    'borrow_date' => $this->borrowDate,
                    'expected_return_date' => $this->expectedReturnDate,
                    'notes' => $this->borrowNotes,
                ]
            );

            $this->dispatch('notify', type: 'success', message: 'Asset loan created successfully.');
            $this->closeCreateModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to create loan: ' . $e->getMessage());
        }
    }

    /**
     * Open return loan modal
     */
    public function openReturnModal(int $loanId): void
    {
        $this->returnLoanId = $loanId;
        $this->returnDate = now()->toDateString();
        $this->returnCondition = 'baik';
        $this->returnNotes = '';
        $this->showReturnModal = true;
    }

    /**
     * Close return loan modal
     */
    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->returnLoanId = null;
        $this->returnDate = null;
        $this->returnCondition = 'baik';
        $this->returnNotes = '';
        $this->resetValidation();
    }

    /**
     * Submit return loan
     */
    public function submitReturn(): void
    {
        $this->validate([
            'returnDate' => 'required|date',
            'returnCondition' => 'required|in:baik,rusak',
            'returnNotes' => 'nullable|string|max:1000',
        ]);

        try {
            $loan = AssetLoan::findOrFail($this->returnLoanId);

            app(AssetLoanService::class)->finishLoan(
                $loan,
                [
                    'return_date' => $this->returnDate,
                    'condition_after_return' => $this->returnCondition,
                    'notes' => $this->returnNotes,
                ]
            );

            $this->dispatch('notify', type: 'success', message: 'Asset returned successfully.');
            $this->closeReturnModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to return asset: ' . $e->getMessage());
        }
    }

    /**
     * Get the loan being returned (for modal display)
     */
    #[Computed]
    public function returnLoan()
    {
        if (!$this->returnLoanId) {
            return null;
        }
        return AssetLoan::with(['asset', 'borrower'])->find($this->returnLoanId);
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->overdueFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.asset-loans.asset-loan-index', [
            'loans' => $this->loans,
            'availableAssets' => $this->availableAssets,
            'employees' => $this->employees,
            'summaryMetrics' => $this->summaryMetrics,
            'returnLoan' => $this->returnLoan,
        ]);
    }
}
