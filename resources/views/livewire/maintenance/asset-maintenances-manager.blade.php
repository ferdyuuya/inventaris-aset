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
