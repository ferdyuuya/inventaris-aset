<div>
    <div class="space-y-6">
        {{-- ============================================== --}}
        {{-- ASSET DETAIL PAGE - UI ONLY (DUMMY DATA)      --}}
        {{-- Backend logic will be reintroduced later      --}}
        {{-- ============================================== --}}

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column (2/3 width on large screens) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- ============================== --}}
            {{-- A. Asset Basic Information     --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Asset Information</flux:heading>
                <flux:text class="mt-2">Basic details about this asset</flux:text>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    {{-- Left Column --}}
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Asset Code</flux:text>
                            <flux:text class="font-mono font-medium">{{ $asset->asset_code }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Asset Name</flux:text>
                            <flux:text class="font-medium">{{ $asset->name }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Category</flux:text>
                            <flux:badge color="zinc">{{ $asset->category->name ?? 'N/A' }}</flux:badge>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="space-y-4">
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Purchase Price</flux:text>
                            <flux:text class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Procurement Date</flux:text>
                            <flux:text class="font-medium">{{ $asset->purchase_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                        </div>
                        <div class="flex justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-zinc-500">Condition</flux:text>
                            @switch($asset->condition)
                                @case('baik')
                                    <flux:badge color="green">Good</flux:badge>
                                    @break
                                @case('rusak')
                                    <flux:badge color="red">Damaged</flux:badge>
                                    @break
                                @case('perlu_perbaikan')
                                    <flux:badge color="yellow">Needs Repair</flux:badge>
                                    @break
                                @default
                                    <flux:badge color="zinc">Unknown</flux:badge>
                            @endswitch
                        </div>
                    </div>
                </div>
            </flux:card>

            {{-- ============================== --}}
            {{-- C. Asset History Log           --}}
            {{-- (Location Transfer History)    --}}
            {{-- ============================== --}}
            <flux:card class="overflow-hidden">
                <div class="px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="lg">Location History</flux:heading>
                    <flux:text class="mt-2">Asset transfer history between locations</flux:text>
                </div>

                <div class="px-6 py-6">
                    @if($locationHistory && $locationHistory->count() > 0)
                        <ul role="list" class="-mb-8">
                            @foreach($locationHistory as $index => $transaction)
                                <li>
                                    <div class="relative @if(!$loop->last) pb-8 @endif">
                                        @if(!$loop->last)
                                            {{-- <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-zinc-200 dark:bg-zinc-700" aria-hidden="true"></span> --}}
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-4 ring-white dark:ring-zinc-900">
                                                    <flux:icon.arrows-right-left class="size-4 text-white" />
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <flux:text>
                                                        Transferred from <span class="font-medium">{{ $transaction->fromLocation->name ?? 'Unknown' }}</span> to <span class="font-medium">{{ $transaction->toLocation->name ?? 'Unknown' }}</span>
                                                    </flux:text>
                                                    <flux:text size="sm" class="text-zinc-500 mt-1">
                                                        Performed by: {{ $transaction->creator->name ?? 'System' }}
                                                        @if($transaction->description)
                                                            • {{ $transaction->description }}
                                                        @endif
                                                    </flux:text>
                                                </div>
                                                <flux:text size="sm" class="text-zinc-500 whitespace-nowrap">
                                                    {{ $transaction->transaction_date?->format('d M Y') ?? 'N/A' }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-8">
                            <flux:icon.inbox class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                            <flux:text class="mt-2 text-zinc-500">No location history available</flux:text>
                        </div>
                    @endif
                </div>
            </flux:card>

            {{-- ============================== --}}
            {{-- D. Borrowing History           --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Borrowing History</flux:heading>
                <flux:text class="mt-2">Record of asset loans to employees</flux:text>

                <div class="mt-6">
                @if($borrowingHistory && $borrowingHistory->count() > 0)
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Borrower</flux:table.column>
                        <flux:table.column>Borrow Date</flux:table.column>
                        <flux:table.column>Expected Return</flux:table.column>
                        <flux:table.column>Return Date</flux:table.column>
                        <flux:table.column>Condition</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($borrowingHistory as $loan)
                        <flux:table.row>
                            <flux:table.cell class="font-medium">{{ $loan->borrower->name ?? 'Unknown' }}</flux:table.cell>
                            <flux:table.cell>{{ $loan->loan_date->format('d M Y') }}</flux:table.cell>
                            <flux:table.cell>{{ $loan->expected_return_date?->format('d M Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $loan->return_date?->format('d M Y') ?? '-' }}</flux:table.cell>
                            <flux:table.cell>
                                @if($loan->condition_after_return)
                                    @if($loan->condition_after_return === 'baik')
                                        <flux:badge color="green" size="sm">Good</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">Damaged</flux:badge>
                                    @endif
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
                                @endif
                            </flux:table.cell>
                        </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
                
                {{-- Pagination --}}
                @if($borrowingHistory->hasPages())
                <div class="mt-4">
                    {{ $borrowingHistory->links() }}
                </div>
                @endif
                @else
                <div class="text-center py-8">
                    <flux:icon.inbox class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="mt-2 text-zinc-500">No borrowing history available</flux:text>
                </div>
                @endif
                </div>
            </flux:card>
            <flux:card>
                <flux:heading size="lg">Maintenance History</flux:heading>
                <flux:text class="mt-2">Service and repair records for this asset</flux:text>

                <div class="mt-6">
                @if($this->maintenanceHistory && $this->maintenanceHistory->count() > 0)
                    <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Date</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                                <flux:table.column>Result</flux:table.column>
                                <flux:table.column>Performed By</flux:table.column>
                                <flux:table.column>Actions</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($this->maintenanceHistory as $maintenance)
                                <flux:table.row 
                                    class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                                    wire:click="viewMaintenanceDetail({{ $maintenance->id }})"
                                >
                                    <flux:table.cell>
                                        <div class="flex items-center gap-1">
                                            <flux:icon.calendar class="size-3 text-gray-400" />
                                            <flux:text size="sm">{{ $maintenance->maintenance_date?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @switch($maintenance->status)
                                            @case('dalam_proses')
                                                <flux:badge color="yellow" size="sm">
                                                    <flux:icon.wrench-screwdriver class="size-3 mr-1" />
                                                    In Progress
                                                </flux:badge>
                                                @break
                                            @case('selesai')
                                                <flux:badge color="green" size="sm">
                                                    <flux:icon.check-circle class="size-3 mr-1" />
                                                    Completed
                                                </flux:badge>
                                                @break
                                            @case('dibatalkan')
                                                <flux:badge color="zinc" size="sm" variant="soft">
                                                    <flux:icon.x-circle class="size-3 mr-1" />
                                                    Cancelled
                                                </flux:badge>
                                                @break
                                        @endswitch
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($maintenance->status === 'selesai' && $maintenance->result)
                                            @if($maintenance->result === 'baik')
                                                <flux:badge color="green" size="sm" variant="soft">Good</flux:badge>
                                            @elseif($maintenance->result === 'rusak')
                                                <flux:badge color="red" size="sm" variant="soft">Damaged</flux:badge>
                                            @else
                                                <flux:badge color="zinc" size="sm" variant="soft">{{ $maintenance->result }}</flux:badge>
                                            @endif
                                        @else
                                            <flux:text size="sm" variant="subtle">—</flux:text>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text size="sm">{{ $maintenance->creator->name ?? 'System' }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell onclick="event.stopPropagation()">
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="eye"
                                            wire:click="viewMaintenanceDetail({{ $maintenance->id }})"
                                        />
                                    </flux:table.cell>
                                </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>

                    {{-- Pagination --}}
                    @if($this->maintenanceHistory->hasPages())
                    <div class="mt-4">
                        {{ $this->maintenanceHistory->links() }}
                    </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <flux:icon.wrench-screwdriver class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                        <flux:text class="mt-2 text-zinc-500">No maintenance history available</flux:text>
                    </div>
                @endif
                </div>
            </flux:card>


        </div>

        {{-- Right Column (1/3 width on large screens) --}}
        <div class="space-y-6">

            {{-- ============================== --}}
            {{-- B. Current Asset State         --}}
            {{-- ============================== --}}
            <flux:card>
                <flux:heading size="lg">Current Status</flux:heading>

                <div class="space-y-4 mt-4">
                    {{-- Status --}}
                    <div class="flex items-center justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:text class="text-zinc-500">Status</flux:text>
                        @switch($asset->status)
                            @case('aktif')
                                <flux:badge color="green" size="lg">Active</flux:badge>
                                @break
                            @case('dipinjam')
                                <flux:badge color="purple" size="lg">Borrowed</flux:badge>
                                @break
                            @case('dipelihara')
                                <flux:badge color="yellow" size="lg">Maintenance</flux:badge>
                                @break
                            @case('nonaktif')
                                <flux:badge color="red" size="lg">Inactive</flux:badge>
                                @break
                            @case('dihapuskan')
                                <flux:badge color="zinc" size="lg">Disposed</flux:badge>
                                @break
                            @default
                                <flux:badge color="zinc" size="lg">Unknown</flux:badge>
                        @endswitch
                    </div>

                    {{-- Current Location --}}
                    <div class="flex items-center justify-between py-3 border-b border-zinc-200 dark:border-zinc-700">
                        <flux:text class="text-zinc-500">Location</flux:text>
                        <div class="flex items-center gap-2">
                            <flux:icon.map-pin class="size-4 text-zinc-400" />
                            <flux:text class="font-medium">{{ $asset->location->name ?? 'N/A' }}</flux:text>
                        </div>
                    </div>

                    {{-- Availability --}}
                    <div class="flex items-center justify-between py-3">
                        <flux:text class="text-zinc-500">Available for Loan</flux:text>
                        @if($asset->is_available)
                            <flux:badge color="green">Yes</flux:badge>
                        @else
                            <flux:badge color="red">No</flux:badge>
                        @endif
                    </div>
                </div>

                @if(!$asset->isDisposed())
                {{-- Borrow Asset Button - Only visible when asset is available (Admin Only) --}}
                @if($canBorrow && auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    class="w-full mt-6"
                    icon="arrow-right-circle"
                    wire:click="openBorrowModal"
                >
                    Borrow Asset
                </flux:button>
                @endif

                {{-- Return Asset Button - Only visible when asset is borrowed (Admin Only) --}}
                @if($asset->isBorrowed() && $activeLoan && auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    class="w-full mt-6"
                    icon="arrow-uturn-left"
                    wire:click="openReturnModal"
                >
                    Return Asset
                </flux:button>
                @endif

                {{-- Transfer Location Button (Admin Only) --}}
                @if($asset->is_available && auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    class="w-full mt-3"
                    icon="arrow-right"
                    wire:click="openTransferModal"
                >
                    Transfer Location
                </flux:button>
                @endif

                {{-- Request Maintenance Button (All Users - Staff can request) --}}
                <flux:button
                    variant="primary"
                    class="w-full mt-3"
                    icon="wrench-screwdriver"
                    wire:click="openRequestMaintenanceModal"
                >
                    Request Maintenance
                </flux:button>

                {{-- Inspect Asset Button (Admin Only) --}}
                @if($canInspect && auth()->user()->isAdmin())
                <flux:button
                    variant="primary"
                    class="w-full mt-3"
                    icon="clipboard-document-check"
                    wire:click="openInspectModal"
                >
                    Inspect Asset
                </flux:button>
                @endif

                {{-- Dispose Asset Button (Admin only, if asset can be disposed) --}}
                @if($canDispose && auth()->user()->isAdmin())
                <flux:button
                    variant="danger"
                    class="w-full mt-3"
                    icon="trash"
                    wire:click="openDisposeModal"
                >
                    Dispose Asset
                </flux:button>
                @endif
                @endif
            </flux:card>

            {{-- ============================== --}}
            {{-- CURRENTLY BORROWED CARD        --}}
            {{-- (Shown when asset is borrowed) --}}
            {{-- ============================== --}}
            @if($asset->isBorrowed() && $activeLoan)
            <flux:card class="space-y-4 border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <div class="flex items-center gap-2">
                    <flux:icon.arrow-right-circle class="size-5 text-purple-500" />
                    <flux:heading size="lg">Currently Borrowed</flux:heading>
                </div>

                @if($activeLoan->isOverdue())
                <div class="p-3 bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 rounded-lg">
                    <div class="flex items-center gap-2">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                        <flux:text class="font-medium text-red-700 dark:text-red-300">This loan is overdue!</flux:text>
                    </div>
                </div>
                @endif

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Borrower</flux:text>
                        <flux:text class="font-medium">{{ $activeLoan->borrower->name ?? 'Unknown' }}</flux:text>
                    </div>
                    @if($activeLoan->borrower->position)
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Position</flux:text>
                        <flux:text>{{ $activeLoan->borrower->position }}</flux:text>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Borrowed Since</flux:text>
                        <flux:text>{{ $activeLoan->loan_date->format('d M Y') }}</flux:text>
                    </div>
                    @if($activeLoan->expected_return_date)
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Expected Return</flux:text>
                        <flux:text class="{{ $activeLoan->isOverdue() ? 'text-red-600 dark:text-red-400 font-medium' : '' }}">
                            {{ $activeLoan->expected_return_date->format('d M Y') }}
                            @if($activeLoan->getDaysUntilReturn() !== null)
                                @if($activeLoan->getDaysUntilReturn() < 0)
                                    ({{ abs($activeLoan->getDaysUntilReturn()) }} days overdue)
                                @elseif($activeLoan->getDaysUntilReturn() == 0)
                                    (Due today)
                                @else
                                    ({{ $activeLoan->getDaysUntilReturn() }} days left)
                                @endif
                            @endif
                        </flux:text>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Duration</flux:text>
                        <flux:text>{{ $activeLoan->getDurationDays() }} days</flux:text>
                    </div>
                    @if($activeLoan->notes)
                    <div>
                        <flux:text class="text-zinc-500">Notes</flux:text>
                        <flux:text class="mt-1 p-2 bg-white dark:bg-zinc-900 rounded border border-zinc-200 dark:border-zinc-700 text-sm">
                            {{ $activeLoan->notes }}
                        </flux:text>
                    </div>
                    @endif
                </div>

                <flux:button variant="primary" class="w-full" icon="arrow-uturn-left" wire:click="openReturnModal">
                    Return Asset
                </flux:button>
            </flux:card>
            @endif

            {{-- ============================== --}}
            {{-- DISPOSED ASSET INFO CARD       --}}
            {{-- (Shown when asset is disposed) --}}
            {{-- ============================== --}}
            @if($asset->isDisposed() && $disposalRecord)
            <flux:card class="space-y-4 border-zinc-400 dark:border-zinc-600 bg-zinc-100 dark:bg-zinc-800/50">
                <div class="flex items-center gap-2">
                    <flux:icon.archive-box-x-mark class="size-5 text-zinc-500" />
                    <flux:heading size="lg" class="text-zinc-700 dark:text-zinc-300">Asset Disposed</flux:heading>
                </div>

                <div class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <flux:text size="sm" class="text-red-700 dark:text-red-300 font-medium">
                        This asset has been permanently disposed and cannot be used for any operations.
                    </flux:text>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Disposed By</flux:text>
                        <flux:text class="font-medium">{{ $disposalRecord->disposedBy->name ?? 'Unknown' }}</flux:text>
                    </div>
                    <div class="flex justify-between">
                        <flux:text class="text-zinc-500">Disposal Date</flux:text>
                        <flux:text>{{ $disposalRecord->disposed_at->format('d M Y, H:i') }}</flux:text>
                    </div>
                    <div>
                        <flux:text class="text-zinc-500">Reason</flux:text>
                        <flux:text class="mt-1 p-2 bg-white dark:bg-zinc-900 rounded border border-zinc-200 dark:border-zinc-700">
                            {{ $disposalRecord->reason }}
                        </flux:text>
                    </div>
                </div>
            </flux:card>
            @endif

            {{-- ============================== --}}
            {{-- B.2. Asset QR Code             --}}
            {{-- ============================== --}}
            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Asset QR Code</flux:heading>
                    <flux:badge color="blue" size="sm">Unique ID</flux:badge>
                </div>
                <flux:text class="mt-2">Unique identifier - Scan to track this asset</flux:text>

                <div class="mt-6 flex flex-col items-center justify-center space-y-4">
                    {{-- QR Code Container - Backend generated from asset_code --}}
                    @if($qrCodeBase64)
                        <div class="p-4 bg-white dark:bg-zinc-800 rounded-lg border-2 border-zinc-200 dark:border-zinc-700">
                            <img src="{{ $qrCodeBase64 }}" alt="QR Code for {{ $asset->asset_code }}" class="w-48 h-48" />
                        </div>
                    @else
                        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border-2 border-red-200 dark:border-red-700 text-center">
                            <flux:text class="text-red-600">QR Code generation failed</flux:text>
                        </div>
                    @endif

                    {{-- Asset Code Display - The unique identifier --}}
                    <div class="text-center">
                        <flux:text size="sm" class="text-zinc-500">Unique Asset Code</flux:text>
                        <flux:text class="font-mono font-bold text-lg mt-1">{{ $asset->asset_code }}</flux:text>
                    </div>

                    {{-- Download Button --}}
                    <flux:button variant="primary" icon="arrow-down-tray" wire:click="downloadQRCode">
                        Download QR Code as {{ $asset->asset_code }}.png
                    </flux:button>
                </div>
            </flux:card>

            {{-- Quick Stats Card --}}
            <flux:card>
                <flux:heading size="lg">Quick Stats</flux:heading>

                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-blue-600 dark:text-blue-400">3</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Total Loans</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-green-600 dark:text-green-400">2</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Transfers</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-yellow-600 dark:text-yellow-400">1</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Maintenance</flux:text>
                    </div>
                    <div class="text-center p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <flux:heading size="xl" class="text-zinc-600 dark:text-zinc-400">2y</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">Asset Age</flux:text>
                    </div>
                </div>
            </flux:card>

            {{-- Maintenance History Card --}}
            {{-- <flux:card>
                <flux:heading size="lg">Maintenance History</flux:heading>

                <div class="space-y-3 mt-4">
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <flux:text class="font-medium">Screen Repair</flux:text>
                            <flux:badge color="green" size="sm">Completed</flux:badge>
                        </div>
                        <flux:text size="sm" class="text-zinc-500">Aug 2025 • 5 days</flux:text>
                    </div>
                </div>

                {{-- Empty State (alternative) --}}
                {{--
                <div class="text-center py-6">
                    <flux:icon.wrench-screwdriver class="mx-auto size-10 text-zinc-300 dark:text-zinc-600" />
                    <flux:text class="mt-2 text-zinc-500">No maintenance history</flux:text>
                </div>
                --}}
            {{-- </flux:card> --}}
        </div>
    </div>
</div>

{{-- ============================================== --}}
{{-- VIEW MAINTENANCE DETAIL MODAL                 --}}
{{-- ============================================== --}}
<flux:modal wire:model.self="showMaintenanceDetailModal" class="md:w-full md:max-w-md">
    <div class="space-y-6">
        {{-- Modal Header --}}
        <div>
            <flux:heading size="lg">Maintenance Detail</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Record #{{ $selectedMaintenanceRecord?->id ?? 'N/A' }}</flux:text>
        </div>

        @if($selectedMaintenanceRecord)
            {{-- Asset Information Section --}}
            <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset Information</flux:label>
                <flux:text class="font-medium">
                    {{ $asset->asset_code }} - {{ $asset->name }}
                </flux:text>
            </div>

            {{-- Status Section --}}
            <div class="flex items-center justify-between">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Status</flux:label>
                @switch($selectedMaintenanceRecord->status)
                    @case('dalam_proses')
                        <flux:badge color="yellow">
                            <flux:icon.wrench-screwdriver class="size-3 mr-1" />
                            In Progress
                        </flux:badge>
                        @break
                    @case('selesai')
                        <flux:badge color="green">
                            <flux:icon.check-circle class="size-3 mr-1" />
                            Completed
                        </flux:badge>
                        @break
                    @case('dibatalkan')
                        <flux:badge color="zinc" variant="soft">
                            <flux:icon.x-circle class="size-3 mr-1" />
                            Cancelled
                        </flux:badge>
                        @break
                @endswitch
            </div>

            {{-- Maintenance Period Section --}}
            <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Maintenance Period</flux:label>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Start Date</flux:label>
                        <div class="flex items-center gap-1 mt-1">
                            <flux:icon.calendar class="size-3 text-gray-400" />
                            <flux:text size="sm">{{ $selectedMaintenanceRecord->maintenance_date?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                        </div>
                    </div>
                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Est. Completion</flux:label>
                        <div class="flex items-center gap-1 mt-1">
                            <flux:icon.clock class="size-3 text-gray-400" />
                            <flux:text size="sm">{{ $selectedMaintenanceRecord->estimated_completion_date?->format('d M Y') ?? 'Not set' }}</flux:text>
                        </div>
                    </div>
                </div>

                @if($selectedMaintenanceRecord->completed_date)
                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Completion Date</flux:label>
                        <div class="flex items-center gap-1 mt-1">
                            <flux:icon.check-circle class="size-3 text-green-500" />
                            <flux:text size="sm">{{ $selectedMaintenanceRecord->completed_date->format('d M Y, H:i') }}</flux:text>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Description Section --}}
            @if($selectedMaintenanceRecord->description)
                <div class="space-y-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Description</flux:label>
                    <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedMaintenanceRecord->description }}</p>
                </div>
            @endif

            {{-- Result & Feedback Section (only for completed maintenance) --}}
            @if($selectedMaintenanceRecord->status === 'selesai' && ($selectedMaintenanceRecord->result || $selectedMaintenanceRecord->feedback))
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Maintenance Outcome</flux:label>
                    
                    @if($selectedMaintenanceRecord->result)
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Result</flux:label>
                            <div class="mt-1">
                                @if($selectedMaintenanceRecord->result === 'baik')
                                    <flux:badge color="green">Good Condition</flux:badge>
                                @elseif($selectedMaintenanceRecord->result === 'rusak')
                                    <flux:badge color="red">Still Damaged</flux:badge>
                                @else
                                    <flux:badge color="zinc">{{ $selectedMaintenanceRecord->result }}</flux:badge>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($selectedMaintenanceRecord->feedback)
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Technical Feedback</flux:label>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedMaintenanceRecord->feedback }}</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Created By Section --}}
            <div class="space-y-2 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Record Information</flux:label>
                
                <div class="flex items-center justify-between">
                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Created By</flux:label>
                        <flux:text size="sm" class="mt-1">{{ $selectedMaintenanceRecord->creator->name ?? 'System' }}</flux:text>
                    </div>
                    @if($selectedMaintenanceRecord->maintenanceRequest)
                        <flux:badge color="blue" size="sm" variant="soft">
                            Request #{{ $selectedMaintenanceRecord->maintenanceRequest->id }}
                        </flux:badge>
                    @endif
                </div>
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex justify-end border-t border-zinc-200 dark:border-zinc-700 pt-4">
            <flux:button variant="ghost" wire:click="closeMaintenanceDetailModal">
                Close
            </flux:button>
        </div>
    </div>
</flux:modal>

{{-- ============================================== --}}
{{-- BORROW ASSET MODAL                           --}}
{{-- ============================================== --}}
<flux:modal wire:model.self="showBorrowModal" class="md:w-96" @close="closeBorrowModal">
    <form wire:submit="submitBorrow" class="space-y-6">
        <div>
            <flux:heading size="lg">Borrow Asset</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Assign this asset to an employee</flux:text>
        </div>

        {{-- Asset Info (Read-only Display) --}}
        <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:label class="text-zinc-700 dark:text-zinc-300">Asset</flux:label>
            <flux:text class="font-medium">{{ $asset->asset_code }} - {{ $asset->name }}</flux:text>
        </div>

        {{-- Employee Selection (Required) --}}
        <div>
            <flux:field>
                <flux:label>Employee <span class="text-red-500">*</span></flux:label>
                <flux:select
                    wire:model="borrowEmployeeId"
                    placeholder="Select an employee"
                >
                    <option value="">-- Choose an employee --</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}">
                            {{ $employee->name }} {{ $employee->position ? '(' . $employee->position . ')' : '' }}
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="borrowEmployeeId" />
            </flux:field>
        </div>

        {{-- Borrow Date (Required) --}}
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

        {{-- Expected Return Date (Optional) --}}
        <div>
            <flux:field>
                <flux:label>Expected Return Date <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                <flux:input
                    type="date"
                    wire:model="borrowExpectedReturnDate"
                />
                <flux:error name="borrowExpectedReturnDate" />
            </flux:field>
        </div>

        {{-- Notes (Optional) --}}
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
            >
                Confirm Borrow
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- ============================================== --}}
{{-- RETURN ASSET MODAL                            --}}
{{-- ============================================== --}}
<flux:modal wire:model.self="showReturnModal" class="md:w-96" @close="closeReturnModal">
    <form wire:submit="submitReturn" class="space-y-6">
        <div>
            <flux:heading size="lg">Return Asset</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Complete this loan and return the asset</flux:text>
        </div>

        {{-- Asset Info (Read-only Display) --}}
        <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:label class="text-zinc-700 dark:text-zinc-300">Asset</flux:label>
            <flux:text class="font-medium">{{ $asset->asset_code }} - {{ $asset->name }}</flux:text>
            @if($activeLoan)
            <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:text size="sm" class="text-zinc-500">Borrowed by: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $activeLoan->borrower->name ?? 'Unknown' }}</span></flux:text>
                <flux:text size="sm" class="text-zinc-500">Since: {{ $activeLoan->loan_date->format('d M Y') }}</flux:text>
            </div>
            @endif
        </div>

        {{-- Return Date (Required) --}}
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

        {{-- Condition After Return (Required) --}}
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
                            <flux:text size="sm" class="text-zinc-500">Asset is in good working condition</flux:text>
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
                            <flux:text size="sm" class="text-zinc-500">Asset has sustained damage</flux:text>
                        </div>
                    </label>
                </div>
                <flux:error name="returnCondition" />
            </flux:field>
        </div>

        {{-- Notes (Optional) --}}
        <div>
            <flux:field>
                <flux:label>Notes <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                <flux:textarea
                    wire:model="returnNotes"
                    placeholder="Add any notes about the return (e.g., damage description)..."
                    rows="3"
                />
                <flux:error name="returnNotes" />
            </flux:field>
        </div>

        {{-- Info Notice --}}
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                <strong>Note:</strong> The asset's condition will be updated based on your selection.
                The asset will become available for borrowing again after return.
            </flux:text>
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

{{-- ============================================== --}}
{{-- TRANSFER LOCATION MODAL                       --}}
{{-- ============================================== --}}
<flux:modal wire:model.self="showTransferModal" class="md:w-96" @close="closeTransferModal">
    <form wire:submit="submitTransfer" class="space-y-6">
        <div>
            <flux:heading size="lg">Transfer Asset Location</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Move this asset to a different location</flux:text>
        </div>

        {{-- From Location (Read-only) --}}
        <div>
            <flux:field>
                <flux:label>From Location</flux:label>
                <flux:input
                    type="text"
                    disabled
                    value="{{ $asset->location->name ?? 'Unknown' }}"
                    placeholder="Current location"
                />
            </flux:field>
        </div>

        {{-- To Location (Dropdown) --}}
        <div>
            <flux:field>
                <flux:label>To Location</flux:label>
                <flux:select
                    wire:model.live="transferLocationId"
                    placeholder="Select destination location"
                >
                    <option value="">-- Choose a location --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}">
                            {{ $location->name }}
                        </option>
                    @endforeach
                </flux:select>
                <flux:error name="transferLocationId" />
            </flux:field>
        </div>

        {{-- Transfer Date --}}
        <div>
            <flux:field>
                <flux:label>Transfer Date</flux:label>
                <flux:input
                    type="date"
                    wire:model="transferDate"
                />
                <flux:error name="transferDate" />
            </flux:field>
        </div>

        {{-- Notes (Optional) --}}
        <div>
            <flux:field>
                <flux:label>Notes <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                <flux:textarea
                    wire:model="transferNotes"
                    placeholder="Add any notes about this transfer..."
                    rows="3"
                />
                <flux:error name="transferNotes" />
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
                Transfer Asset
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- ============================================== --}}
{{-- QUICK MAINTENANCE REQUEST MODAL               --}}
{{-- ============================================== --}}
<flux:modal wire:model.defer="showRequestMaintenanceModal" class="md:w-96" @close="closeRequestMaintenanceModal">
    <form wire:submit="submitRequestMaintenance" class="space-y-6">
        <div>
            <flux:heading size="lg">Request Maintenance</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Submit a maintenance request for this asset</flux:text>
        </div>

        {{-- Asset Info (Read-only Display) --}}
        <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:label class="text-zinc-700 dark:text-zinc-300">Asset</flux:label>
            <flux:text class="font-medium">{{ $asset->asset_code }} - {{ $asset->name }}</flux:text>
        </div>

        {{-- Description (Required) --}}
        <div>
            <flux:field>
                <flux:label>Describe the Issue <span class="text-red-500">*</span></flux:label>
                <flux:textarea
                    wire:model.live="requestMaintenanceDescription"
                    placeholder="Describe the maintenance issue or reason..."
                    rows="4"
                />
                <flux:error name="requestMaintenanceDescription" />
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
                Submit Request
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- ============================================== --}}
{{-- MAINTENANCE REQUEST CONFIRMATION MODAL        --}}
{{-- ============================================== --}}
<flux:modal wire:model="showRequestMaintenanceConfirmation" class="md:w-full md:max-w-sm">
    <div class="space-y-6 text-center">
        {{-- Success Icon --}}
        <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-green-100 dark:bg-green-900/30">
            <flux:icon.check-circle class="size-10 text-green-600 dark:text-green-400" />
        </div>

        {{-- Success Message --}}
        <div>
            <flux:heading size="lg">Maintenance Request Submitted</flux:heading>
            <flux:text class="mt-2 text-zinc-500 dark:text-zinc-400">
                Your maintenance request has been successfully submitted and is waiting for admin approval.
            </flux:text>
        </div>

        {{-- Action Button --}}
        <div class="pt-4">
            <flux:button
                variant="filled"
                color="green"
                wire:click="closeRequestMaintenanceConfirmation"
                class="w-full"
            >
                Close
            </flux:button>
        </div>
    </div>
</flux:modal>

{{-- ============================================== --}}
{{-- DISPOSE ASSET MODAL                           --}}
{{-- ============================================== --}}
<flux:modal wire:model.defer="showDisposeModal" class="md:w-96" @close="closeDisposeModal">
    <form wire:submit="submitDispose" class="space-y-6">
        <div>
            <flux:heading size="lg" class="text-red-600 dark:text-red-400">Dispose Asset</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Permanently retire this asset from operational use</flux:text>
        </div>

        {{-- Strong Warning --}}
        <div class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-lg">
            <div class="flex items-start gap-3">
                <flux:icon.exclamation-triangle class="size-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                <div>
                    <flux:text class="font-semibold text-red-700 dark:text-red-300">Warning: This action is IRREVERSIBLE</flux:text>
                    <flux:text size="sm" class="text-red-600 dark:text-red-400 mt-1">
                        Once disposed, this asset:
                    </flux:text>
                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 mt-1 space-y-1">
                        <li>Cannot be borrowed</li>
                        <li>Cannot be maintained</li>
                        <li>Cannot be transferred</li>
                        <li>Cannot be restored</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Asset Info (Read-only Display) --}}
        <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:label class="text-zinc-700 dark:text-zinc-300">Asset to Dispose</flux:label>
            <flux:text class="font-medium">{{ $asset->asset_code }} - {{ $asset->name }}</flux:text>
        </div>

        {{-- Disposal Reason (Required) --}}
        <div>
            <flux:field>
                <flux:label>Disposal Reason <span class="text-red-500">*</span></flux:label>
                <flux:textarea
                    wire:model="disposeReason"
                    placeholder="Explain why this asset is being disposed (e.g., beyond repair, obsolete, lost, etc.)..."
                    rows="4"
                />
                <flux:error name="disposeReason" />
                <flux:description>Minimum 5 characters. This reason will be recorded for audit purposes.</flux:description>
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
                variant="danger"
            >
                Confirm Disposal
            </flux:button>
        </div>
    </form>
</flux:modal>

{{-- ============================================== --}}
{{-- INSPECT ASSET MODAL                           --}}
{{-- ============================================== --}}
<flux:modal wire:model.defer="showInspectModal" class="md:w-96">
    <form wire:submit="submitInspection" class="space-y-6">
        <div>
            <flux:heading size="lg">Inspect Asset</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Evaluate and record asset condition</flux:text>
        </div>

        {{-- Asset Info (Read-only Display) --}}
        <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <flux:label class="text-zinc-700 dark:text-zinc-300">Asset</flux:label>
            <flux:text class="font-medium">{{ $asset->asset_code }} - {{ $asset->name }}</flux:text>
            <div class="flex items-center gap-2 mt-1">
                <flux:text size="sm" class="text-zinc-500">Current condition:</flux:text>
                @switch($asset->condition)
                    @case('baik')
                        <flux:badge color="green" size="sm">Good</flux:badge>
                        @break
                    @case('rusak')
                        <flux:badge color="red" size="sm">Damaged</flux:badge>
                        @break
                    @case('perlu_perbaikan')
                        <flux:badge color="yellow" size="sm">Needs Repair</flux:badge>
                        @break
                    @default
                        <flux:badge color="zinc" size="sm">Unknown</flux:badge>
                @endswitch
            </div>
        </div>

        {{-- Condition Result --}}
        <div>
            <flux:field>
                <flux:label>Condition Result <span class="text-red-500">*</span></flux:label>
                <div class="flex flex-col gap-2 mt-2">
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <input 
                            type="radio" 
                            wire:model="inspectCondition" 
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
                            wire:model="inspectCondition" 
                            value="perlu_perbaikan" 
                            class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500"
                        >
                        <div class="flex items-center gap-2">
                            <flux:badge color="yellow">Needs Repair</flux:badge>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                        <input 
                            type="radio" 
                            wire:model="inspectCondition" 
                            value="rusak" 
                            class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                        >
                        <div class="flex items-center gap-2">
                            <flux:badge color="red">Damaged (Rusak)</flux:badge>
                        </div>
                    </label>
                </div>
                <flux:error name="inspectCondition" />
            </flux:field>
        </div>

        {{-- Description / Notes --}}
        <div>
            <flux:field>
                <flux:label>Notes <span class="text-zinc-500 text-sm">(optional)</span></flux:label>
                <flux:textarea
                    wire:model="inspectDescription"
                    placeholder="Add any notes about the inspection findings..."
                    rows="3"
                />
                <flux:error name="inspectDescription" />
            </flux:field>
        </div>

        {{-- Info Notice --}}
        <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <flux:text size="sm" class="text-blue-700 dark:text-blue-300">
                <strong>Note:</strong> This inspection will update the asset's condition field only. 
                It will NOT change the asset's status or availability.
            </flux:text>
        </div>

        {{-- Action Buttons --}}
        <div class="flex gap-3 justify-end">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">
                    Cancel
                </flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">
                Record Inspection
            </flux:button>
        </div>
    </form>
</flux:modal>
</div>
