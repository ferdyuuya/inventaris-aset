<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Asset Maintenances</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Track active and completed asset maintenance records</flux:subheading>
        </div>
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
        <div class="flex-1 max-w-md">
            <flux:input
                wire:model.live="search"
                type="text"
                placeholder="Search by asset code or name..."
                icon="magnifying-glass"
                clearable
                class="text-gray-900 dark:text-white"
            />
        </div>

        {{-- Vertical Separator --}}
        <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

        {{-- Status Filter --}}
        <flux:dropdown position="bottom" align="start">
            <flux:button
                variant="ghost"
                size="sm"
                icon="funnel"
                :badge="$filterStatus ? '1' : null"
            >
                Status
            </flux:button>

            <flux:menu>
                <flux:menu.item
                    wire:click="$set('filterStatus', '')"
                    :class="!$filterStatus ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                >
                    <span :class="!$filterStatus ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                        All Statuses
                    </span>
                </flux:menu.item>
                <flux:separator />
                <flux:menu.item
                    wire:click="$set('filterStatus', 'dalam_proses')"
                    :class="$filterStatus === 'dalam_proses' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                >
                    <span :class="$filterStatus === 'dalam_proses' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                        In Progress
                    </span>
                </flux:menu.item>
                <flux:menu.item
                    wire:click="$set('filterStatus', 'selesai')"
                    :class="$filterStatus === 'selesai' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                >
                    <span :class="$filterStatus === 'selesai' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                        Completed
                    </span>
                </flux:menu.item>
                <flux:menu.item
                    wire:click="$set('filterStatus', 'dibatalkan')"
                    :class="$filterStatus === 'dibatalkan' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                >
                    <span :class="$filterStatus === 'dibatalkan' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                        Cancelled
                    </span>
                </flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </div>

    <flux:separator />

    {{-- Asset Maintenances Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>Maintenance ID</flux:table.column>
                <flux:table.column>Asset</flux:table.column>
                <flux:table.column>Start Date</flux:table.column>
                <flux:table.column>Est. Completion</flux:table.column>
                <flux:table.column>Completion Date</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Request ID</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($maintenances as $maintenance)
                    <flux:table.row>
                        <flux:table.cell class="font-mono text-sm">
                            #{{ $maintenance->id }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $maintenance->asset->asset_code ?? 'N/A' }}</span>
                                <span class="text-sm text-zinc-500">{{ $maintenance->asset->name ?? 'Unknown Asset' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $maintenance->maintenance_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $maintenance->estimated_completion_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">
                                @if($maintenance->completed_date)
                                    {{ $maintenance->completed_date->format('d M Y') }}
                                @else
                                    <span class="text-zinc-400">Pending</span>
                                @endif
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            @switch($maintenance->status)
                                @case('dalam_proses')
                                    <flux:badge color="blue">In Progress</flux:badge>
                                    @break
                                @case('selesai')
                                    <flux:badge color="green">Completed</flux:badge>
                                    @break
                                @case('dibatalkan')
                                    <flux:badge color="red">Cancelled</flux:badge>
                                    @break
                            @endswitch
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($maintenance->maintenanceRequest)
                                <span class="text-sm font-mono">#{{ $maintenance->maintenanceRequest->id }}</span>
                            @else
                                <span class="text-sm text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="viewMaintenance({{ $maintenance->id }})"
                                />

                                @if($maintenance->status === 'dalam_proses')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="check"
                                        class="text-green-600 dark:text-green-400"
                                        wire:click="openCompleteModal({{ $maintenance->id }})"
                                        title="Complete Maintenance"
                                    />
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="x-mark"
                                        class="text-red-600 dark:text-red-400"
                                        wire:click="openCancelModal({{ $maintenance->id }})"
                                        title="Cancel Maintenance"
                                    />
                                @endif
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center py-8">
                            <flux:icon.inbox class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                            <p class="text-zinc-500">No maintenance records found</p>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $maintenances->links() }}
    </div>

    {{-- ============================================== --}}
    {{-- VIEW MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Maintenance Record Details</flux:heading>
                <flux:text class="mt-2">Maintenance #{{ $selectedMaintenance?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedMaintenance)
                <div class="space-y-4">
                    <div>
                        <flux:label>Asset</flux:label>
                        <flux:field>
                            <span class="text-sm font-medium">
                                {{ $selectedMaintenance->asset->asset_code }} - {{ $selectedMaintenance->asset->name }}
                            </span>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Start Date</flux:label>
                        <flux:field>
                            <span class="text-sm">{{ $selectedMaintenance->maintenance_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Estimated Completion</flux:label>
                        <flux:field>
                            <span class="text-sm">{{ $selectedMaintenance->estimated_completion_date?->format('d M Y') ?? 'N/A' }}</span>
                        </flux:field>
                    </div>

                    @if($selectedMaintenance->completed_date)
                        <div>
                            <flux:label>Completion Date</flux:label>
                            <flux:field>
                                <span class="text-sm">{{ $selectedMaintenance->completed_date->format('d M Y') }}</span>
                            </flux:field>
                        </div>
                    @endif

                    <div>
                        <flux:label>Description</flux:label>
                        <flux:field>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $selectedMaintenance->description ?? 'No description' }}</p>
                        </flux:field>
                    </div>

                    <div>
                        <flux:label>Status</flux:label>
                        <flux:field>
                            @switch($selectedMaintenance->status)
                                @case('dalam_proses')
                                    <flux:badge color="blue">In Progress</flux:badge>
                                    @break
                                @case('selesai')
                                    <flux:badge color="green">Completed</flux:badge>
                                    @break
                                @case('dibatalkan')
                                    <flux:badge color="red">Cancelled</flux:badge>
                                    @break
                            @endswitch
                        </flux:field>
                    </div>

                    @if($selectedMaintenance->maintenanceRequest)
                        <div>
                            <flux:label>Request ID</flux:label>
                            <flux:field>
                                <span class="text-sm font-mono">#{{ $selectedMaintenance->maintenanceRequest->id }}</span>
                            </flux:field>
                        </div>
                    @endif

                    @if($selectedMaintenance->creator)
                        <div>
                            <flux:label>Created By</flux:label>
                            <flux:field>
                                <span class="text-sm">{{ $selectedMaintenance->creator->name }}</span>
                            </flux:field>
                        </div>
                    @endif
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeModals">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- COMPLETE MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showCompleteModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Mark Maintenance as Completed?</flux:heading>
                <flux:text class="mt-2">Are you sure you want to mark this maintenance as completed?</flux:text>
            </div>

            @if($selectedMaintenance)
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <p class="text-sm text-green-900 dark:text-green-100">
                        <strong>{{ $selectedMaintenance->asset->asset_code }}</strong> - {{ $selectedMaintenance->asset->name }}
                    </p>
                    <p class="text-sm text-green-700 dark:text-green-200 mt-1">
                        This will update the asset status back to "Available".
                    </p>
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeModals">
                    Cancel
                </flux:button>
                <flux:button
                    variant="filled"
                    color="green"
                    wire:click="completeMaintenance"
                >
                    Mark as Completed
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- CANCEL MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showCancelModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Cancel Maintenance?</flux:heading>
                <flux:text class="mt-2">Are you sure you want to cancel this maintenance? This action cannot be undone.</flux:text>
            </div>

            @if($selectedMaintenance)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <p class="text-sm text-red-900 dark:text-red-100">
                        <strong>{{ $selectedMaintenance->asset->asset_code ?? 'N/A' }}</strong> - {{ $selectedMaintenance->asset->name ?? 'Unknown Asset' }}
                    </p>
                    <p class="text-sm text-red-700 dark:text-red-200 mt-1">
                        The asset will be restored to "Active" status. Condition will remain unchanged.
                    </p>
                </div>

                <div>
                    <flux:label for="cancelReason">Reason for Cancellation (Optional)</flux:label>
                    <flux:textarea
                        wire:model="cancelReason"
                        id="cancelReason"
                        rows="3"
                        placeholder="Enter reason for cancelling this maintenance..."
                        class="mt-1"
                    />
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="closeModals">
                    Close
                </flux:button>
                <flux:button
                    variant="filled"
                    color="red"
                    wire:click="cancelMaintenance"
                >
                    Confirm Cancellation
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
