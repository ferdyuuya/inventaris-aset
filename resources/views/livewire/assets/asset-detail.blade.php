<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.x-circle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <flux:button href="{{ route('assets.index') }}" variant="ghost" icon="arrow-left">
                    Back
                </flux:button>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ $asset->name }}
                        </h1>
                        @switch($asset->status)
                            @case('aktif')
                                <flux:badge color="green">Active</flux:badge>
                                @break
                            @case('dipinjam')
                                <flux:badge color="purple">Borrowed</flux:badge>
                                @break
                            @case('dipelihara')
                                <flux:badge color="yellow">Maintenance</flux:badge>
                                @break
                            @case('nonaktif')
                                <flux:badge color="red">Inactive</flux:badge>
                                @break
                        @endswitch
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-mono">
                        {{ $asset->asset_code }}
                    </p>
                </div>
            </div>
            
            {{-- Action Buttons --}}
            <div class="flex items-center space-x-2">
                @if(in_array('transfer_location', $availableActions))
                    <flux:modal.trigger name="transferLocation">
                        <flux:button variant="ghost" icon="arrows-right-left" wire:click="openTransferModal">
                            Transfer Location
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                @if(in_array('borrow', $availableActions))
                    <flux:modal.trigger name="borrowAsset">
                        <flux:button variant="ghost" icon="arrow-right-circle" wire:click="openBorrowModal">
                            Borrow Asset
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                @if(in_array('return_borrow', $availableActions))
                    <flux:button variant="primary" icon="arrow-uturn-left" wire:click="returnAsset" wire:confirm="Are you sure you want to return this asset?">
                        Return Asset
                    </flux:button>
                @endif

                @if(in_array('send_maintenance', $availableActions))
                    <flux:modal.trigger name="sendMaintenance">
                        <flux:button variant="ghost" icon="wrench-screwdriver" wire:click="openMaintenanceModal">
                            Send to Maintenance
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                @if(in_array('complete_maintenance', $availableActions))
                    <flux:button variant="primary" icon="check-circle" wire:click="completeMaintenance" wire:confirm="Are you sure maintenance is complete?">
                        Complete Maintenance
                    </flux:button>
                @endif
            </div>
        </div>
    </div>

    {{-- Current Status Alert (if borrowed or under maintenance) --}}
    @if($currentBorrower)
        <div class="bg-purple-50 dark:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.arrow-right-circle class="h-5 w-5 text-purple-500" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-purple-800 dark:text-purple-200">
                        Currently Borrowed
                    </h3>
                    <div class="mt-2 text-sm text-purple-700 dark:text-purple-300">
                        <p>
                            <strong>Borrower:</strong> {{ $currentBorrower->borrower->name ?? 'Unknown' }} 
                            | <strong>Since:</strong> {{ $currentBorrower->loan_date?->format('d M Y') }}
                            @if($currentBorrower->expected_return_date)
                                | <strong>Expected Return:</strong> {{ $currentBorrower->expected_return_date->format('d M Y') }}
                                @if($currentBorrower->isOverdue())
                                    <flux:badge color="red" size="sm" class="ml-2">Overdue</flux:badge>
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($currentMaintenance)
        <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.wrench-screwdriver class="h-5 w-5 text-yellow-500" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        Under Maintenance
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                        <p>
                            <strong>Since:</strong> {{ $currentMaintenance->maintenance_date?->format('d M Y') }}
                            @if($currentMaintenance->estimated_completion_date)
                                | <strong>Estimated Completion:</strong> {{ $currentMaintenance->estimated_completion_date->format('d M Y') }}
                                @if($currentMaintenance->isOverdue())
                                    <flux:badge color="red" size="sm" class="ml-2">Overdue</flux:badge>
                                @endif
                            @endif
                        </p>
                        <p class="mt-1"><strong>Reason:</strong> {{ $currentMaintenance->description }}</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg border border-gray-200 dark:border-gray-700">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button 
                    wire:click="setTab('details')" 
                    class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'details' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                >
                    <flux:icon.document-text class="h-4 w-4 inline mr-2" />
                    Details
                </button>
                <button 
                    wire:click="setTab('history')" 
                    class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'history' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                >
                    <flux:icon.clock class="h-4 w-4 inline mr-2" />
                    History
                </button>
                <button 
                    wire:click="setTab('borrowing')" 
                    class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'borrowing' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                >
                    <flux:icon.arrow-right-circle class="h-4 w-4 inline mr-2" />
                    Borrowing
                </button>
                <button 
                    wire:click="setTab('maintenance')" 
                    class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'maintenance' ? 'border-blue-500 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                >
                    <flux:icon.wrench-screwdriver class="h-4 w-4 inline mr-2" />
                    Maintenance
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">
            {{-- Details Tab --}}
            @if($activeTab === 'details')
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Basic Information --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Basic Information</h3>
                        <dl class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Asset Code</dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-mono">{{ $asset->asset_code }}</dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Asset Name</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $asset->name }}</dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    <flux:badge color="zinc">{{ $asset->category?->name ?? 'N/A' }}</flux:badge>
                                </dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Location</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
                                    <span class="flex items-center">
                                        <flux:icon.map-pin class="h-4 w-4 mr-1 text-gray-400" />
                                        {{ $asset->location?->name ?? 'N/A' }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Condition</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">
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
                                    @endswitch
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Purchase Information --}}
                    <div class="space-y-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Purchase Information</h3>
                        <dl class="space-y-4">
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Date</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $asset->purchase_date?->format('d M Y') ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Purchase Price</dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-semibold">
                                    Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}
                                </dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number</dt>
                                <dd class="text-sm text-gray-900 dark:text-white font-mono">{{ $asset->invoice_number ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</dt>
                                <dd class="text-sm text-gray-900 dark:text-white">{{ $asset->supplier?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="text-sm">
                                    @switch($asset->status)
                                        @case('aktif')
                                            <flux:badge color="green">Active</flux:badge>
                                            @break
                                        @case('dipinjam')
                                            <flux:badge color="purple">Borrowed</flux:badge>
                                            @break
                                        @case('dipelihara')
                                            <flux:badge color="yellow">Maintenance</flux:badge>
                                            @break
                                        @case('nonaktif')
                                            <flux:badge color="red">Inactive</flux:badge>
                                            @break
                                    @endswitch
                                </dd>
                            </div>
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Available</dt>
                                <dd class="text-sm">
                                    @if($asset->is_available)
                                        <flux:badge color="green">Yes</flux:badge>
                                    @else
                                        <flux:badge color="red">No</flux:badge>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Read-Only Notice --}}
                <div class="mt-6 bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <flux:icon.lock-closed class="h-5 w-5 text-gray-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                All fields are read-only. Asset information is derived from procurement records and cannot be manually edited.
                                Use the action buttons above to perform lifecycle operations (transfer, borrow, maintenance).
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- History Tab --}}
            @if($activeTab === 'history')
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Location Transfer History</h3>
                    
                    @if($locationHistory && count($locationHistory) > 0)
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($locationHistory as $index => $transaction)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                                        <flux:icon.arrows-right-left class="h-4 w-4 text-white" />
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            Transferred from 
                                                            <span class="font-medium text-gray-900 dark:text-white">{{ $transaction->fromLocation?->name ?? 'N/A' }}</span>
                                                            to 
                                                            <span class="font-medium text-gray-900 dark:text-white">{{ $transaction->toLocation?->name ?? 'N/A' }}</span>
                                                        </p>
                                                        @if($transaction->description)
                                                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">
                                                                Reason: {{ $transaction->description }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                                        <time datetime="{{ $transaction->transaction_date }}">
                                                            {{ $transaction->transaction_date?->format('d M Y') }}
                                                        </time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.arrows-right-left class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No transfer history</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This asset has not been transferred between locations yet.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Borrowing Tab --}}
            @if($activeTab === 'borrowing')
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Borrowing History</h3>
                    
                    @if($borrowingHistory && count($borrowingHistory) > 0)
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Borrower</flux:table.column>
                                <flux:table.column>Loan Date</flux:table.column>
                                <flux:table.column>Expected Return</flux:table.column>
                                <flux:table.column>Actual Return</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($borrowingHistory as $loan)
                                    <flux:table.row :key="$loan->id">
                                        <flux:table.cell class="font-medium">
                                            {{ $loan->borrower?->name ?? 'Unknown' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $loan->loan_date?->format('d M Y') }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $loan->expected_return_date?->format('d M Y') ?? '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $loan->return_date?->format('d M Y') ?? '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @switch($loan->status)
                                                @case('dipinjam')
                                                    <flux:badge color="purple">Borrowed</flux:badge>
                                                    @if($loan->isOverdue())
                                                        <flux:badge color="red" size="sm" class="ml-1">Overdue</flux:badge>
                                                    @endif
                                                    @break
                                                @case('dikembalikan')
                                                    <flux:badge color="green">Returned</flux:badge>
                                                    @break
                                                @case('hilang')
                                                    <flux:badge color="red">Lost</flux:badge>
                                                    @break
                                            @endswitch
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.arrow-right-circle class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No borrowing history</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This asset has never been borrowed.
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Maintenance Tab --}}
            @if($activeTab === 'maintenance')
                <div class="space-y-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Maintenance History</h3>
                    
                    @if($maintenanceHistory && count($maintenanceHistory) > 0)
                        <flux:table>
                            <flux:table.columns>
                                <flux:table.column>Start Date</flux:table.column>
                                <flux:table.column>Description</flux:table.column>
                                <flux:table.column>Est. Completion</flux:table.column>
                                <flux:table.column>Completed</flux:table.column>
                                <flux:table.column>Status</flux:table.column>
                            </flux:table.columns>
                            <flux:table.rows>
                                @foreach($maintenanceHistory as $maintenance)
                                    <flux:table.row :key="$maintenance->id">
                                        <flux:table.cell>
                                            {{ $maintenance->maintenance_date?->format('d M Y') }}
                                        </flux:table.cell>
                                        <flux:table.cell class="max-w-xs truncate">
                                            {{ $maintenance->description }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $maintenance->estimated_completion_date?->format('d M Y') ?? '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $maintenance->completed_date?->format('d M Y') ?? '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            @switch($maintenance->status)
                                                @case('dalam_proses')
                                                    <flux:badge color="yellow">In Progress</flux:badge>
                                                    @if($maintenance->isOverdue())
                                                        <flux:badge color="red" size="sm" class="ml-1">Overdue</flux:badge>
                                                    @endif
                                                    @break
                                                @case('selesai')
                                                    <flux:badge color="green">Completed</flux:badge>
                                                    @break
                                                @case('dibatalkan')
                                                    <flux:badge color="zinc">Cancelled</flux:badge>
                                                    @break
                                            @endswitch
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.wrench-screwdriver class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No maintenance history</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                This asset has never been sent for maintenance.
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Transfer Location Modal --}}
    <flux:modal name="transferLocation" class="md:w-96">
        <form wire:submit="submitTransfer" class="space-y-6">
            <div>
                <flux:heading size="lg">Transfer Asset Location</flux:heading>
                <flux:text class="mt-2 text-sm">Move this asset to a new location.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:text class="text-sm mb-2">
                        <strong>Current Location:</strong> {{ $asset->location?->name ?? 'N/A' }}
                    </flux:text>
                </div>

                <div>
                    <flux:select 
                        wire:model="transferLocationId" 
                        label="New Location" 
                        description="Select the destination location"
                        required
                    >
                        <flux:select.option value="">-- Select Location --</flux:select.option>
                        @foreach($locations as $location)
                            <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('transferLocationId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:textarea 
                        wire:model="transferReason" 
                        label="Reason for Transfer" 
                        description="Provide a reason for this transfer"
                        placeholder="Enter transfer reason..."
                        rows="3"
                        required
                    />
                    @error('transferReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeTransferModal">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Transfer Asset</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Borrow Asset Modal --}}
    <flux:modal name="borrowAsset" class="md:w-96">
        <form wire:submit="submitBorrow" class="space-y-6">
            <div>
                <flux:heading size="lg">Borrow Asset</flux:heading>
                <flux:text class="mt-2 text-sm">Assign this asset to an employee.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:select 
                        wire:model="borrowEmployeeId" 
                        label="Borrower" 
                        description="Select the employee borrowing this asset"
                        required
                    >
                        <flux:select.option value="">-- Select Employee --</flux:select.option>
                        @foreach($employees as $employee)
                            <flux:select.option value="{{ $employee->id }}">
                                {{ $employee->name }} ({{ $employee->position ?? 'N/A' }})
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('borrowEmployeeId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input 
                        type="date" 
                        wire:model="borrowReturnDate" 
                        label="Expected Return Date" 
                        description="When should the asset be returned (optional)"
                        min="{{ now()->format('Y-m-d') }}"
                    />
                    @error('borrowReturnDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeBorrowModal">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Confirm Borrow</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Send to Maintenance Modal --}}
    <flux:modal name="sendMaintenance" class="md:w-96">
        <form wire:submit="submitMaintenance" class="space-y-6">
            <div>
                <flux:heading size="lg">Send to Maintenance</flux:heading>
                <flux:text class="mt-2 text-sm">Submit this asset for maintenance or repair.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:textarea 
                        wire:model="maintenanceReason" 
                        label="Maintenance Reason" 
                        description="Describe why maintenance is needed"
                        placeholder="Enter maintenance reason..."
                        rows="3"
                        required
                    />
                    @error('maintenanceReason') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input 
                        type="date" 
                        wire:model="maintenanceEstimatedDate" 
                        label="Estimated Completion Date" 
                        description="Expected date when maintenance will be complete (optional)"
                        min="{{ now()->format('Y-m-d') }}"
                    />
                    @error('maintenanceEstimatedDate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost" wire:click="closeMaintenanceModal">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Submit to Maintenance</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
