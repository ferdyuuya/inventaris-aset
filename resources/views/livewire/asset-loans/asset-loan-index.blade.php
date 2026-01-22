<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <flux:heading size="xl">Asset Loans</flux:heading>
        <flux:text class="mt-1 text-zinc-500">Manage asset borrowings and returns</flux:text>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <flux:card class="text-center">
            <flux:text class="text-zinc-500">Total Loans</flux:text>
            <flux:heading size="xl" class="text-zinc-700 dark:text-zinc-300">{{ $summaryMetrics['total_loans'] }}</flux:heading>
        </flux:card>
        <flux:card class="text-center">
            <flux:text class="text-zinc-500">Active Loans</flux:text>
            <flux:heading size="xl" class="text-purple-600 dark:text-purple-400">{{ $summaryMetrics['active_loans'] }}</flux:heading>
        </flux:card>
        <flux:card class="text-center">
            <flux:text class="text-zinc-500">Completed</flux:text>
            <flux:heading size="xl" class="text-green-600 dark:text-green-400">{{ $summaryMetrics['completed_loans'] }}</flux:heading>
        </flux:card>
        <flux:card class="text-center">
            <flux:text class="text-zinc-500">Overdue</flux:text>
            <flux:heading size="xl" class="text-red-600 dark:text-red-400">{{ $summaryMetrics['overdue_loans'] }}</flux:heading>
        </flux:card>
    </div>

    {{-- Filters and Actions --}}
    <flux:card class="mb-6">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
            <div class="flex flex-col md:flex-row gap-4 items-start md:items-center flex-1">
                {{-- Search --}}
                <div class="w-full md:w-64">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search asset or borrower..."
                        icon="magnifying-glass"
                    />
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="dipinjam">Active (Dipinjam)</option>
                        <option value="selesai">Completed (Selesai)</option>
                    </flux:select>
                </div>

                {{-- Overdue Filter --}}
                <div class="w-full md:w-48">
                    <flux:select wire:model.live="overdueFilter">
                        <option value="">All Loans</option>
                        <option value="overdue">Overdue Only</option>
                    </flux:select>
                </div>

                {{-- Clear Filters --}}
                @if($search || $statusFilter || $overdueFilter)
                <flux:button variant="ghost" size="sm" wire:click="clearFilters">
                    Clear Filters
                </flux:button>
                @endif
            </div>

            {{-- Create Loan Button --}}
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                New Loan
            </flux:button>
        </div>
    </flux:card>

    {{-- Loans Table --}}
    <flux:card>
        @if($loans->count() > 0)
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Asset</flux:table.column>
                <flux:table.column>Borrower</flux:table.column>
                <flux:table.column>Borrow Date</flux:table.column>
                <flux:table.column>Expected Return</flux:table.column>
                <flux:table.column>Return Date</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>
                @foreach($loans as $loan)
                <flux:table.row>
                    <flux:table.cell>
                        <div>
                            <a href="{{ route('assets.show', $loan->asset) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline" wire:navigate>
                                {{ $loan->asset->asset_code }}
                            </a>
                            <flux:text size="sm" class="text-zinc-500">{{ $loan->asset->name }}</flux:text>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div>
                            <flux:text class="font-medium">{{ $loan->borrower->name ?? 'Unknown' }}</flux:text>
                            @if($loan->borrower->position)
                            <flux:text size="sm" class="text-zinc-500">{{ $loan->borrower->position }}</flux:text>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $loan->loan_date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        @if($loan->expected_return_date)
                            <span class="{{ $loan->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                {{ $loan->expected_return_date->format('d M Y') }}
                            </span>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($loan->return_date)
                            {{ $loan->return_date->format('d M Y') }}
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($loan->status === 'dipinjam')
                            <flux:badge color="purple">Borrowed</flux:badge>
                            @if($loan->isOverdue())
                                <flux:badge color="red" size="sm" class="ml-1">Overdue</flux:badge>
                            @endif
                        @else
                            <flux:badge color="green">Returned</flux:badge>
                            @if($loan->condition_after_return === 'rusak')
                                <flux:badge color="red" size="sm" class="ml-1">Damaged</flux:badge>
                            @endif
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($loan->isActive())
                        <flux:button variant="primary" size="sm" wire:click="openReturnModal({{ $loan->id }})">
                            Return
                        </flux:button>
                        @else
                        <flux:text size="sm" class="text-zinc-400">Completed</flux:text>
                        @endif
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        {{-- Pagination --}}
        @if($loans->hasPages())
        <div class="mt-4 px-4">
            {{ $loans->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-12">
            <flux:icon.inbox class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
            <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No loans found</flux:heading>
            <flux:text class="mt-2 text-zinc-500">
                @if($search || $statusFilter || $overdueFilter)
                    Try adjusting your filters or search term.
                @else
                    Get started by creating your first asset loan.
                @endif
            </flux:text>
            @if(!$search && !$statusFilter && !$overdueFilter)
            <flux:button variant="primary" class="mt-4" icon="plus" wire:click="openCreateModal">
                Create First Loan
            </flux:button>
            @endif
        </div>
        @endif
    </flux:card>

    {{-- ============================================== --}}
    {{-- CREATE LOAN MODAL                             --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.self="showCreateModal" class="md:w-[480px]" @close="closeCreateModal">
        <form wire:submit="submitCreate" class="space-y-6">
            <div>
                <flux:heading size="lg">Create Asset Loan</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Assign an asset to an employee</flux:text>
            </div>

            {{-- Asset Selection --}}
            <div>
                <flux:field>
                    <flux:label>Asset <span class="text-red-500">*</span></flux:label>
                    <flux:select
                        wire:model="selectedAssetId"
                        placeholder="Select an asset"
                    >
                        <option value="">-- Choose an asset --</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->asset_code }} - {{ $asset->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="selectedAssetId" />
                    @if($availableAssets->isEmpty())
                        <flux:description class="text-yellow-600 dark:text-yellow-400">
                            No assets available for borrowing.
                        </flux:description>
                    @endif
                </flux:field>
            </div>

            {{-- Employee Selection --}}
            <div>
                <flux:field>
                    <flux:label>Employee <span class="text-red-500">*</span></flux:label>
                    <flux:select
                        wire:model="selectedEmployeeId"
                        placeholder="Select an employee"
                    >
                        <option value="">-- Choose an employee --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">
                                {{ $employee->name }} {{ $employee->position ? '(' . $employee->position . ')' : '' }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="selectedEmployeeId" />
                </flux:field>
            </div>

            {{-- Borrow Date --}}
            <div>
                <flux:field>
                    <flux:label>Borrow Date <span class="text-red-500">*</span></flux:label>
                    <flux:input
                        type="date"
                        wire:model="borrowDate"
                    />
                    <flux:error name="borrowDate" />
                </flux:field>
            </div>

            {{-- Expected Return Date --}}
            <div>
                <flux:field>
                    <flux:label>Expected Return Date <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                    <flux:input
                        type="date"
                        wire:model="expectedReturnDate"
                    />
                    <flux:error name="expectedReturnDate" />
                </flux:field>
            </div>

            {{-- Notes --}}
            <div>
                <flux:field>
                    <flux:label>Notes <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                    <flux:textarea
                        wire:model="borrowNotes"
                        placeholder="Add any notes about this loan..."
                        rows="3"
                    />
                    <flux:error name="borrowNotes" />
                </flux:field>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
                        Cancel
                    </flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                    :disabled="$availableAssets->isEmpty()"
                >
                    Create Loan
                </flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- RETURN LOAN MODAL                             --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.self="showReturnModal" class="md:w-96" @close="closeReturnModal">
        <form wire:submit="submitReturn" class="space-y-6">
            <div>
                <flux:heading size="lg">Return Asset</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Complete this loan and return the asset</flux:text>
            </div>

            {{-- Loan Info (Read-only Display) --}}
            @if($returnLoan)
            <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <flux:label class="text-zinc-700 dark:text-zinc-300">Asset</flux:label>
                <flux:text class="font-medium">{{ $returnLoan->asset->asset_code }} - {{ $returnLoan->asset->name }}</flux:text>
                <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:text size="sm" class="text-zinc-500">Borrowed by: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $returnLoan->borrower->name ?? 'Unknown' }}</span></flux:text>
                    <flux:text size="sm" class="text-zinc-500">Since: {{ $returnLoan->loan_date->format('d M Y') }}</flux:text>
                    @if($returnLoan->isOverdue())
                    <div class="mt-2">
                        <flux:badge color="red" size="sm">Overdue by {{ abs($returnLoan->getDaysUntilReturn()) }} days</flux:badge>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Return Date --}}
            <div>
                <flux:field>
                    <flux:label>Return Date <span class="text-red-500">*</span></flux:label>
                    <flux:input
                        type="date"
                        wire:model="returnDate"
                    />
                    <flux:error name="returnDate" />
                </flux:field>
            </div>

            {{-- Condition After Return --}}
            <div>
                <flux:field>
                    <flux:label>Condition After Return <span class="text-red-500">*</span></flux:label>
                    <div class="flex flex-col gap-2 mt-2">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input 
                                type="radio" 
                                wire:model="returnCondition" 
                                value="baik" 
                                class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                            >
                            <div class="flex items-center gap-2">
                                <flux:badge color="green">Good (Baik)</flux:badge>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input 
                                type="radio" 
                                wire:model="returnCondition" 
                                value="rusak" 
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                            >
                            <div class="flex items-center gap-2">
                                <flux:badge color="red">Damaged (Rusak)</flux:badge>
                            </div>
                        </label>
                    </div>
                    <flux:error name="returnCondition" />
                </flux:field>
            </div>

            {{-- Notes --}}
            <div>
                <flux:field>
                    <flux:label>Notes <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                    <flux:textarea
                        wire:model="returnNotes"
                        placeholder="Add any notes about the return..."
                        rows="3"
                    />
                    <flux:error name="returnNotes" />
                </flux:field>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 justify-end">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">
                        Cancel
                    </flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                >
                    Confirm Return
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
