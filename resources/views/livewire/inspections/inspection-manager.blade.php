<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Asset Inspections</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Record and track asset condition evaluations</flux:subheading>
        </div>

        {{-- Create Inspection Button --}}
        <flux:button
            variant="primary"
            icon="plus"
            wire:click="openCreateModal"
        >
            New Inspection
        </flux:button>
    </div>

    <flux:separator />

    {{-- Search Bar --}}
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
    </div>

    <flux:separator />

    {{-- Inspections Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column>ID</flux:table.column>
                <flux:table.column>Asset</flux:table.column>
                <flux:table.column>Condition Before</flux:table.column>
                <flux:table.column>Condition After</flux:table.column>
                <flux:table.column>Inspector</flux:table.column>
                <flux:table.column>Inspected At</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($inspections as $inspection)
                    <flux:table.row>
                        <flux:table.cell class="font-mono text-sm">
                            #{{ $inspection->id }}
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $inspection->asset->asset_code ?? 'N/A' }}</span>
                                <span class="text-sm text-zinc-500">{{ $inspection->asset->name ?? 'Unknown Asset' }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            @if($inspection->condition_before)
                                @switch($inspection->condition_before)
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
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </flux:table.cell>

                        <flux:table.cell>
                            @switch($inspection->condition_after)
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
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $inspection->inspector->name ?? 'Unknown' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-sm">{{ $inspection->inspected_at?->format('d M Y, H:i') ?? 'N/A' }}</span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="eye"
                                    wire:click="viewInspection({{ $inspection->id }})"
                                    title="View Details"
                                />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <flux:icon.clipboard-document-check class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-2" />
                            <p class="text-zinc-500">No inspection records found</p>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    <div>
        {{ $inspections->links() }}
    </div>

    {{-- ============================================== --}}
    {{-- VIEW INSPECTION MODAL                         --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Inspection Details</flux:heading>
                <flux:text class="mt-2">Inspection #{{ $selectedInspection?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedInspection)
                <div class="space-y-4">
                    {{-- Asset Info --}}
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <flux:label class="text-zinc-500">Asset</flux:label>
                        <flux:text class="font-medium mt-1">
                            {{ $selectedInspection->asset->asset_code }} - {{ $selectedInspection->asset->name }}
                        </flux:text>
                    </div>

                    {{-- Condition Change --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <flux:label>Condition Before</flux:label>
                            <div class="mt-1">
                                @if($selectedInspection->condition_before)
                                    @switch($selectedInspection->condition_before)
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
                                @else
                                    <span class="text-zinc-400">Unknown</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <flux:label>Condition After</flux:label>
                            <div class="mt-1">
                                @switch($selectedInspection->condition_after)
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
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <flux:label>Description / Notes</flux:label>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ $selectedInspection->description ?? 'No description provided' }}
                        </flux:text>
                    </div>

                    {{-- Inspector --}}
                    <div>
                        <flux:label>Inspected By</flux:label>
                        <flux:text class="mt-1">{{ $selectedInspection->inspector->name ?? 'Unknown' }}</flux:text>
                    </div>

                    {{-- Date --}}
                    <div>
                        <flux:label>Inspected At</flux:label>
                        <flux:text class="mt-1">{{ $selectedInspection->inspected_at?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                    </div>
                </div>
            @endif

            <div class="flex justify-between gap-3">
                <flux:button
                    variant="danger"
                    size="sm"
                    icon="trash"
                    wire:click="deleteInspection({{ $selectedInspection?->id }})"
                    wire:confirm="Are you sure you want to delete this inspection record? This will not revert the asset condition change."
                >
                    Delete
                </flux:button>
                <flux:button variant="ghost" wire:click="closeViewModal">
                    Close
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- ============================================== --}}
    {{-- CREATE INSPECTION MODAL                       --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showCreateModal">
        <form wire:submit="submitCreateInspection" class="space-y-6">
            <div>
                <flux:heading size="lg">Record Asset Inspection</flux:heading>
                <flux:text class="mt-2">Evaluate and update asset condition</flux:text>
            </div>

            {{-- Asset Selection --}}
            <div>
                <flux:field>
                    <flux:label>Asset <span class="text-red-500">*</span></flux:label>
                    <flux:select
                        wire:model.live="createAssetId"
                        placeholder="Select an asset to inspect"
                    >
                        <option value="">-- Choose an asset --</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->asset_code }} - {{ $asset->name }}
                                (Current: {{ ucfirst(str_replace('_', ' ', $asset->condition ?? 'unknown')) }})
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:error name="createAssetId" />
                </flux:field>
            </div>

            {{-- Condition Result --}}
            <div>
                <flux:field>
                    <flux:label>Condition Result <span class="text-red-500">*</span></flux:label>
                    <div class="flex flex-col gap-2 mt-2">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="baik" 
                                class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                            >
                            <div class="flex items-center gap-2">
                                <flux:badge color="green">Good (Baik)</flux:badge>
                                <span class="text-sm text-zinc-500">Asset is in good working condition</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="perlu_perbaikan" 
                                class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500"
                            >
                            <div class="flex items-center gap-2">
                                <flux:badge color="yellow">Needs Repair (Perlu Perbaikan)</flux:badge>
                                <span class="text-sm text-zinc-500">Asset requires maintenance</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="rusak" 
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                            >
                            <div class="flex items-center gap-2">
                                <flux:badge color="red">Damaged (Rusak)</flux:badge>
                                <span class="text-sm text-zinc-500">Asset is damaged or non-functional</span>
                            </div>
                        </label>
                    </div>
                    <flux:error name="createCondition" />
                </flux:field>
            </div>

            {{-- Description --}}
            <div>
                <flux:field>
                    <flux:label>Description / Notes</flux:label>
                    <flux:textarea
                        wire:model="createDescription"
                        placeholder="Add any notes about the inspection findings..."
                        rows="3"
                    />
                    <flux:error name="createDescription" />
                </flux:field>
            </div>

            {{-- Warning --}}
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <flux:text size="sm" class="text-amber-700 dark:text-amber-300">
                    <strong>Note:</strong> This inspection will update the asset's condition field. 
                    It will NOT change the asset's status or availability.
                </flux:text>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 justify-end">
                <flux:button type="button" variant="ghost" wire:click="closeCreateModal">
                    Cancel
                </flux:button>
                <flux:button type="submit" variant="primary">
                    Record Inspection
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
