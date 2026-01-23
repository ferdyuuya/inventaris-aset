<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Asset Loans</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage asset borrowings and returns</flux:subheading>
        </div>
        @if(auth()->user()->isAdmin())
        <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
            New Loan
        </flux:button>
        @endif
    </div>

    <flux:separator />

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
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

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            {{-- Search Input --}}
            <div class="flex-1 max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search asset or borrower..."
                    icon="magnifying-glass"
                    clearable
                    class="text-gray-900 dark:text-white"
                />
            </div>

            {{-- Vertical Separator --}}
            <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            {{-- Filter Dropdowns --}}
            <div class="flex flex-wrap gap-3">
                {{-- Status Filter --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="funnel"
                        :badge="$statusFilter ? '1' : null"
                    >
                        Status
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('statusFilter', '')"
                            :class="$statusFilter === '' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                        >
                            <span :class="$statusFilter === '' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                                All Statuses
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        <flux:menu.item
                            wire:click="$set('statusFilter', 'dipinjam')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $statusFilter === 'dipinjam',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $statusFilter === 'dipinjam',
                            ])>
                                Active (Dipinjam)
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('statusFilter', 'selesai')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $statusFilter === 'selesai',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $statusFilter === 'selesai',
                            ])>
                                Completed (Selesai)
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Overdue Filter --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="funnel"
                        :badge="$overdueFilter ? '1' : null"
                    >
                        Overdue
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('overdueFilter', '')"
                            :class="$overdueFilter === '' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                        >
                            <span :class="$overdueFilter === '' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                                All Loans
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        <flux:menu.item
                            wire:click="$set('overdueFilter', 'overdue')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $overdueFilter === 'overdue',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $overdueFilter === 'overdue',
                            ])>
                                Overdue Only
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters Button --}}
                @if($search || $statusFilter || $overdueFilter)
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                        wire:click="clearFilters"
                    >
                        Clear
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Active Filters Summary --}}
        @if($search || $statusFilter || $overdueFilter)
            <div class="flex flex-wrap gap-2 items-center text-sm">
                <flux:text class="text-gray-600 dark:text-gray-400">Active filters:</flux:text>
                @if($search)
                    <flux:badge color="blue" size="sm">
                        Search: <strong>{{ $search }}</strong>
                    </flux:badge>
                @endif
                @if($statusFilter)
                    <flux:badge color="blue" size="sm">
                        Status: <strong>{{ $statusFilter === 'dipinjam' ? 'Active' : 'Completed' }}</strong>
                    </flux:badge>
                @endif
                @if($overdueFilter)
                    <flux:badge color="blue" size="sm">
                        <strong>Overdue Only</strong>
                    </flux:badge>
                @endif
            </div>
        @endif
    </div>

    <flux:separator />

    {{-- Loans Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($loans->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
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
                    <flux:table.row 
                        class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                        wire:click="$dispatch('navigate', { url: '{{ route('assets.show', $loan->asset) }}' })"
                    >
                        <flux:table.cell>
                            <flux:text size="sm" variant="subtle">{{ ($loans->currentPage() - 1) * $loans->perPage() + $loop->iteration }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div>
                                <flux:text variant="strong" color="blue">{{ $loan->asset->asset_code }}</flux:text>
                                <flux:text size="sm" class="text-zinc-500">{{ $loan->asset->name }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div>
                                <flux:text variant="strong">{{ $loan->borrower->name ?? 'Unknown' }}</flux:text>
                                @if($loan->borrower->position)
                                <flux:text size="sm" class="text-zinc-500">{{ $loan->borrower->position }}</flux:text>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-1">
                                <flux:icon.calendar class="size-3 text-gray-400" />
                                <flux:text size="sm">{{ $loan->loan_date->format('d M Y') }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($loan->expected_return_date)
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 {{ $loan->isOverdue() ? 'text-red-500' : 'text-gray-400' }}" />
                                    <flux:text size="sm" class="{{ $loan->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                                        {{ $loan->expected_return_date->format('d M Y') }}
                                    </flux:text>
                                </div>
                            @else
                                <flux:text variant="subtle" size="sm">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($loan->return_date)
                                <div class="flex items-center gap-1">
                                    <flux:icon.check-circle class="size-3 text-green-500" />
                                    <flux:text size="sm">{{ $loan->return_date->format('d M Y') }}</flux:text>
                                </div>
                            @else
                                <flux:text variant="subtle" size="sm">-</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($loan->status === 'dipinjam')
                                <flux:badge color="purple" size="sm">
                                    <flux:icon.arrow-right-circle class="size-3 mr-1" />
                                    Borrowed
                                </flux:badge>
                                @if($loan->isOverdue())
                                    <flux:badge color="red" size="sm" class="ml-1">
                                        <flux:icon.exclamation-triangle class="size-3 mr-1" />
                                        Overdue
                                    </flux:badge>
                                @endif
                            @else
                                <flux:badge color="green" size="sm">
                                    <flux:icon.check-circle class="size-3 mr-1" />
                                    Returned
                                </flux:badge>
                                @if($loan->condition_after_return === 'rusak')
                                    <flux:badge color="orange" size="sm" variant="soft" class="ml-1">
                                        Damaged
                                    </flux:badge>
                                @endif
                            @endif
                        </flux:table.cell>
                        <flux:table.cell onclick="event.stopPropagation()">
                            @if($loan->isActive() && auth()->user()->isAdmin())
                                <flux:button variant="primary" size="sm" icon="arrow-uturn-left" wire:click="openReturnModal({{ $loan->id }})">
                                    Return
                                </flux:button>
                            @else
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />

                                    <flux:menu>
                                        <flux:menu.item
                                            href="{{ route('assets.show', $loan->asset) }}"
                                            icon="eye"
                                            wire:navigate
                                        >
                                            View Asset
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            @endif
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
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
        </div>
    </div>

    {{-- Pagination --}}
    @if($loans->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$loans" />
        </div>
    @endif

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
