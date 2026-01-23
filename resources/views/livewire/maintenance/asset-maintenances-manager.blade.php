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
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="flex-1 max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by asset code or name..."
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
                        :badge="$filterStatus ? '1' : null"
                    >
                        Status
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('filterStatus', '')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => !$filterStatus,
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => !$filterStatus,
                            ])>
                                All Statuses
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'dalam_proses')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $filterStatus === 'dalam_proses',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $filterStatus === 'dalam_proses',
                            ])>
                                In Progress
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'selesai')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $filterStatus === 'selesai',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $filterStatus === 'selesai',
                            ])>
                                Completed
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="$set('filterStatus', 'dibatalkan')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $filterStatus === 'dibatalkan',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $filterStatus === 'dibatalkan',
                            ])>
                                Cancelled
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Sorting Dropdown --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                        Sort
                    </flux:button>

                    <flux:menu>
                        <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                        <flux:separator />
                        
                        <flux:menu.item wire:click="$set('sortDirection', 'desc')">
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => ($sortDirection ?? 'desc') === 'desc'])>
                                Newest
                            </span>
                        </flux:menu.item>

                        <flux:menu.item wire:click="$set('sortDirection', 'asc')">
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => ($sortDirection ?? 'desc') === 'asc'])>
                                Oldest
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters --}}
                @if($search || $filterStatus)
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="x-mark"
                        wire:click="$set('search', ''); $set('filterStatus', '')"
                    >
                        Clear
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Active Filters Summary --}}
        @if($search || $filterStatus)
            <div class="flex flex-wrap gap-2 items-center text-sm">
                <flux:text class="text-gray-600 dark:text-gray-400">Active filters:</flux:text>
                @if($search)
                    <flux:badge color="blue" size="sm">
                        Search: <strong>{{ $search }}</strong>
                    </flux:badge>
                @endif
                @if($filterStatus)
                    <flux:badge color="blue" size="sm">
                        Status: <strong>{{ $filterStatus === 'dalam_proses' ? 'In Progress' : ($filterStatus === 'selesai' ? 'Completed' : 'Cancelled') }}</strong>
                    </flux:badge>
                @endif
            </div>
        @endif
    </div>

    <flux:separator />

    {{-- Asset Maintenances Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($maintenances->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Asset</flux:table.column>
                    <flux:table.column>Start Date</flux:table.column>
                    <flux:table.column>Est. Completion</flux:table.column>
                    <flux:table.column>Completion Date</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Request ID</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($maintenances as $maintenance)
                        <flux:table.row 
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="viewMaintenance({{ $maintenance->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle" class="font-mono">#{{ $maintenance->id }}</flux:text>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <flux:text variant="strong" color="blue">{{ $maintenance->asset->asset_code ?? 'N/A' }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-500">{{ $maintenance->asset->name ?? 'Unknown Asset' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $maintenance->maintenance_date?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.clock class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $maintenance->estimated_completion_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($maintenance->completed_date)
                                    <div class="flex items-center gap-1">
                                        <flux:icon.check-circle class="size-3 text-green-500" />
                                        <flux:text size="sm">{{ $maintenance->completed_date->format('d M Y, H:i') }}</flux:text>
                                    </div>
                                @else
                                    <flux:text size="sm" variant="subtle">Pending</flux:text>
                                @endif
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
                                @if($maintenance->maintenanceRequest)
                                    <flux:badge color="blue" size="sm" variant="soft">
                                        #{{ $maintenance->maintenanceRequest->id }}
                                    </flux:badge>
                                @else
                                    <flux:text size="sm" variant="subtle">—</flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell onclick="event.stopPropagation()">
                                <div class="flex gap-1">
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        wire:click="viewMaintenance({{ $maintenance->id }})"
                                    />

                                    @if($maintenance->status === 'dalam_proses' && auth()->user()->isAdmin())
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
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-12">
                <flux:icon.wrench-screwdriver class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No maintenance records found</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    @if($search || $filterStatus)
                        Try adjusting your filters or search term.
                    @else
                        Maintenance records will appear here when assets are sent for maintenance.
                    @endif
                </flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($maintenances->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$maintenances" />
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- VIEW MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Maintenance Record Details</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Maintenance #{{ $selectedMaintenance?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedMaintenance)
                {{-- Asset Information Section --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset Information</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedMaintenance->asset->asset_code }} - {{ $selectedMaintenance->asset->name }}
                    </flux:text>
                </div>

                {{-- Schedule Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Schedule</flux:label>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Start Date</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedMaintenance->maintenance_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                        </div>
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Est. Completion</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedMaintenance->estimated_completion_date?->format('d M Y') ?? 'N/A' }}</flux:text>
                        </div>
                    </div>

                    @if($selectedMaintenance->completed_date)
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Completion Date</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedMaintenance->completed_date->format('d M Y') }}</flux:text>
                        </div>
                    @endif
                </div>

                {{-- Details Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Details</flux:label>
                    
                    <div>
                        <flux:label class="text-zinc-500 dark:text-zinc-400">Description</flux:label>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedMaintenance->description ?? 'No description provided' }}</p>
                    </div>
                </div>

                {{-- Result & Feedback Section (only shown when maintenance is completed) --}}
                @if($selectedMaintenance->status === 'selesai' && ($selectedMaintenance->result || $selectedMaintenance->feedback))
                    <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                        <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Maintenance Outcome</flux:label>
                        
                        @if($selectedMaintenance->result)
                            <div>
                                <flux:label class="text-zinc-500 dark:text-zinc-400">Result</flux:label>
                                <div class="mt-1">
                                    @if($selectedMaintenance->result === 'baik')
                                        <flux:badge color="green">Baik (Good)</flux:badge>
                                    @elseif($selectedMaintenance->result === 'rusak')
                                        <flux:badge color="red">Rusak (Damaged)</flux:badge>
                                    @else
                                        <flux:text class="text-sm">{{ $selectedMaintenance->result }}</flux:text>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($selectedMaintenance->feedback)
                            <div>
                                <flux:label class="text-zinc-500 dark:text-zinc-400">Technical Feedback</flux:label>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">{{ $selectedMaintenance->feedback }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Status Section --}}
                <div class="space-y-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Status & Assignment</flux:label>
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
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
                        </div>
                        
                        @if($selectedMaintenance->maintenanceRequest)
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">
                                Request #{{ $selectedMaintenance->maintenanceRequest->id }}
                            </span>
                        @endif
                    </div>

                    @if($selectedMaintenance->creator)
                        <div>
                            <flux:label class="text-zinc-500 dark:text-zinc-400">Created By</flux:label>
                            <flux:text class="text-sm mt-1">{{ $selectedMaintenance->creator->name }}</flux:text>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- COMPLETE MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showCompleteModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Complete Maintenance</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Record the outcome and complete this maintenance</flux:text>
            </div>

            @if($selectedMaintenance)
                {{-- Asset Information --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedMaintenance->asset->asset_code ?? 'N/A' }} - {{ $selectedMaintenance->asset->name ?? 'Unknown Asset' }}
                    </flux:text>
                </div>

                {{-- Result Selection Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Maintenance Result</flux:label>
                    
                    <div>
                        <flux:label>Asset Condition After Maintenance <span class="text-red-500">*</span></flux:label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model="completeResult" 
                                    value="baik" 
                                    class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                                >
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <flux:badge color="green">Baik (Good)</flux:badge>
                                </span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input 
                                    type="radio" 
                                    wire:model="completeResult" 
                                    value="rusak" 
                                    class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                                >
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <flux:badge color="red">Rusak (Damaged)</flux:badge>
                                </span>
                            </label>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">The asset condition will be updated based on your selection</p>
                    </div>
                </div>

                {{-- Feedback Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Technical Feedback</flux:label>
                    
                    <flux:field>
                        <flux:label for="completeFeedback">Maintenance Notes <span class="text-red-500">*</span></flux:label>
                        <flux:textarea
                            wire:model="completeFeedback"
                            id="completeFeedback"
                            rows="4"
                            placeholder="Describe the maintenance work performed, parts replaced, issues found, etc..."
                        />
                        <flux:description>Provide details about the work performed and any recommendations</flux:description>
                    </flux:field>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Cancel
                </flux:button>
                <flux:button
                    variant="filled"
                    color="green"
                    wire:click="completeMaintenance"
                >
                    Complete Maintenance
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- CANCEL MAINTENANCE MODAL --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showCancelModal" class="md:w-full md:max-w-md">
        <div class="space-y-6">
            {{-- Modal Header --}}
            <div>
                <flux:heading size="lg">Cancel Maintenance</flux:heading>
                <flux:text class="mt-1 text-zinc-500">Confirm cancellation for this maintenance record</flux:text>
            </div>

            @if($selectedMaintenance)
                {{-- Asset Information --}}
                <div class="space-y-2 p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Asset</flux:label>
                    <flux:text class="font-medium">
                        {{ $selectedMaintenance->asset->asset_code ?? 'N/A' }} - {{ $selectedMaintenance->asset->name ?? 'Unknown Asset' }}
                    </flux:text>
                </div>

                {{-- Warning Notice --}}
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-red-700 dark:text-red-200">This action cannot be undone</p>
                            <p class="text-sm text-red-600 dark:text-red-300 mt-1">
                                The asset will be restored to "Active" status. Condition will remain unchanged.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Reason Section --}}
                <div class="space-y-4 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                    <flux:label class="text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Cancellation Details</flux:label>
                    
                    <flux:field>
                        <flux:label for="cancelReason">Reason for Cancellation</flux:label>
                        <flux:textarea
                            wire:model="cancelReason"
                            id="cancelReason"
                            rows="3"
                            placeholder="Enter reason for cancelling this maintenance..."
                        />
                        <flux:description>Optional - provide context for why this maintenance is being cancelled</flux:description>
                    </flux:field>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex justify-end gap-3 border-t border-zinc-200 dark:border-zinc-700 pt-4">
                <flux:button variant="ghost" wire:click="closeModals">
                    Cancel
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
