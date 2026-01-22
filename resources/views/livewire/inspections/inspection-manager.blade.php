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
            icon="clipboard-document-check"
            wire:click="openCreateModal"
        >
            New Inspection
        </flux:button>
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            {{-- Search Input --}}
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

            {{-- Clear Search --}}
            @if($search)
                <flux:button
                    variant="ghost"
                    size="sm"
                    icon="x-mark"
                    wire:click="$set('search', '')"
                >
                    Clear
                </flux:button>
            @endif
        </div>

        {{-- Active Filters Summary --}}
        @if($search)
            <div class="flex flex-wrap gap-2 items-center text-sm">
                <flux:text class="text-gray-600 dark:text-gray-400">Active filters:</flux:text>
                <flux:badge color="blue" size="sm">
                    Search: <strong>{{ $search }}</strong>
                </flux:badge>
            </div>
        @endif
    </div>

    <flux:separator />

    {{-- Inspections Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Asset</flux:table.column>
                    <flux:table.column>Condition Before</flux:table.column>
                    <flux:table.column>Condition After</flux:table.column>
                    <flux:table.column>Inspector</flux:table.column>
                    <flux:table.column>Inspected At</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($inspections as $inspection)
                        <flux:table.row class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle" class="font-mono">#{{ $inspection->id }}</flux:text>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex flex-col">
                                    <flux:text variant="strong" color="blue">{{ $inspection->asset->asset_code ?? 'N/A' }}</flux:text>
                                    <flux:text size="sm" class="text-zinc-500">{{ $inspection->asset->name ?? 'Unknown Asset' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                @if($inspection->condition_before)
                                    @switch($inspection->condition_before)
                                        @case('baik')
                                            <flux:badge color="green" size="sm">
                                                <flux:icon.check-circle class="size-3 mr-1" />
                                                Good
                                            </flux:badge>
                                            @break
                                        @case('rusak')
                                            <flux:badge color="red" size="sm">
                                                <flux:icon.x-circle class="size-3 mr-1" />
                                                Damaged
                                            </flux:badge>
                                            @break
                                        @case('perlu_perbaikan')
                                            <flux:badge color="yellow" size="sm">
                                                <flux:icon.wrench class="size-3 mr-1" />
                                                Needs Repair
                                            </flux:badge>
                                            @break
                                    @endswitch
                                @else
                                    <flux:text variant="subtle" size="sm">—</flux:text>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell>
                                @switch($inspection->condition_after)
                                    @case('baik')
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check-circle class="size-3 mr-1" />
                                            Good
                                        </flux:badge>
                                        @break
                                    @case('rusak')
                                        <flux:badge color="red" size="sm">
                                            <flux:icon.x-circle class="size-3 mr-1" />
                                            Damaged
                                        </flux:badge>
                                        @break
                                    @case('perlu_perbaikan')
                                        <flux:badge color="yellow" size="sm">
                                            <flux:icon.wrench class="size-3 mr-1" />
                                            Needs Repair
                                        </flux:badge>
                                        @break
                                @endswitch
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.user-circle class="size-4 text-gray-400" />
                                    <flux:text size="sm">{{ $inspection->inspector->name ?? 'Unknown' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $inspection->inspected_at?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell>
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item 
                                            icon="eye" 
                                            wire:click="viewInspection({{ $inspection->id }})"
                                        >
                                            View Details
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item 
                                            icon="trash" 
                                            variant="danger"
                                            wire:click="deleteInspection({{ $inspection->id }})"
                                            wire:confirm="Are you sure you want to delete this inspection record?"
                                        >
                                            Delete
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="7" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.clipboard-document-check class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                                    <flux:heading size="lg" class="text-zinc-600 dark:text-zinc-400">No inspections found</flux:heading>
                                    <flux:text class="mt-2 text-zinc-500">
                                        @if($search)
                                            Try adjusting your search term.
                                        @else
                                            Get started by creating your first asset inspection.
                                        @endif
                                    </flux:text>
                                    @if(!$search)
                                        <flux:button variant="primary" class="mt-4" icon="clipboard-document-check" wire:click="openCreateModal">
                                            Create First Inspection
                                        </flux:button>
                                    @endif
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($inspections->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$inspections" />
        </div>
    @endif

    {{-- ============================================== --}}
    {{-- VIEW INSPECTION MODAL                         --}}
    {{-- ============================================== --}}
    <flux:modal wire:model.defer="showViewModal" class="md:w-[480px]">
        <div class="space-y-6">
            <div>
                <div class="flex items-center gap-2">
                    <flux:icon.clipboard-document-check class="size-6 text-blue-500" />
                    <flux:heading size="lg">Inspection Details</flux:heading>
                </div>
                <flux:text class="mt-1 text-zinc-500">Record #{{ $selectedInspection?->id ?? 'N/A' }}</flux:text>
            </div>

            @if($selectedInspection)
                <div class="space-y-4">
                    {{-- Asset Info --}}
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.cube class="size-4 text-blue-500" />
                            <flux:label class="text-zinc-500">Asset</flux:label>
                        </div>
                        <flux:text variant="strong" class="text-lg">
                            {{ $selectedInspection->asset->asset_code }}
                        </flux:text>
                        <flux:text size="sm" class="text-zinc-500">
                            {{ $selectedInspection->asset->name }}
                        </flux:text>
                    </div>

                    {{-- Condition Change --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <flux:label class="text-xs text-zinc-500 mb-2">Condition Before</flux:label>
                            <div>
                                @if($selectedInspection->condition_before)
                                    @switch($selectedInspection->condition_before)
                                        @case('baik')
                                            <flux:badge color="green" size="sm">
                                                <flux:icon.check-circle class="size-3 mr-1" />
                                                Good
                                            </flux:badge>
                                            @break
                                        @case('rusak')
                                            <flux:badge color="red" size="sm">
                                                <flux:icon.x-circle class="size-3 mr-1" />
                                                Damaged
                                            </flux:badge>
                                            @break
                                        @case('perlu_perbaikan')
                                            <flux:badge color="yellow" size="sm">
                                                <flux:icon.wrench class="size-3 mr-1" />
                                                Needs Repair
                                            </flux:badge>
                                            @break
                                    @endswitch
                                @else
                                    <flux:text variant="subtle" size="sm">Unknown</flux:text>
                                @endif
                            </div>
                        </div>
                        <div class="p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-blue-50/50 dark:bg-blue-900/20">
                            <flux:label class="text-xs text-zinc-500 mb-2">Condition After</flux:label>
                            <div>
                                @switch($selectedInspection->condition_after)
                                    @case('baik')
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check-circle class="size-3 mr-1" />
                                            Good
                                        </flux:badge>
                                        @break
                                    @case('rusak')
                                        <flux:badge color="red" size="sm">
                                            <flux:icon.x-circle class="size-3 mr-1" />
                                            Damaged
                                        </flux:badge>
                                        @break
                                    @case('perlu_perbaikan')
                                        <flux:badge color="yellow" size="sm">
                                            <flux:icon.wrench class="size-3 mr-1" />
                                            Needs Repair
                                        </flux:badge>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <flux:icon.document-text class="size-4 text-gray-400" />
                            <flux:label class="text-zinc-500">Description / Notes</flux:label>
                        </div>
                        <div class="p-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                                {{ $selectedInspection->description ?? 'No description provided' }}
                            </flux:text>
                        </div>
                    </div>

                    {{-- Meta Info --}}
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <flux:icon.user-circle class="size-4 text-gray-400" />
                                <flux:label class="text-xs text-zinc-500">Inspected By</flux:label>
                            </div>
                            <flux:text size="sm">{{ $selectedInspection->inspector->name ?? 'Unknown' }}</flux:text>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <flux:icon.calendar class="size-4 text-gray-400" />
                                <flux:label class="text-xs text-zinc-500">Inspected At</flux:label>
                            </div>
                            <flux:text size="sm">{{ $selectedInspection->inspected_at?->format('d M Y, H:i') ?? 'N/A' }}</flux:text>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex justify-between gap-3 pt-2 border-t border-zinc-200 dark:border-zinc-700">
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
    <flux:modal wire:model.defer="showCreateModal" class="md:w-[520px]">
        <form wire:submit="submitCreateInspection" class="space-y-6">
            <div>
                <div class="flex items-center gap-2">
                    <flux:icon.clipboard-document-check class="size-6 text-blue-500" />
                    <flux:heading size="lg">Create Inspection</flux:heading>
                </div>
                <flux:text class="mt-1 text-zinc-500">Evaluate and record asset condition</flux:text>
            </div>

            {{-- Asset Selection --}}
            <div>
                <flux:field>
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.cube class="size-4 text-gray-400" />
                        <flux:label>Select Asset <span class="text-red-500">*</span></flux:label>
                    </div>
                    <flux:select
                        wire:model.live="createAssetId"
                        placeholder="Choose an asset to inspect"
                    >
                        <option value="">-- Choose an asset --</option>
                        @foreach($availableAssets as $asset)
                            <option value="{{ $asset->id }}">
                                {{ $asset->asset_code }} - {{ $asset->name }}
                            </option>
                        @endforeach
                    </flux:select>
                    <flux:description class="mt-1">Select the asset you want to inspect</flux:description>
                    <flux:error name="createAssetId" />
                </flux:field>
            </div>

            {{-- Selected Asset Preview --}}
            @if($createAssetId)
                @php
                    $selectedAsset = $availableAssets->firstWhere('id', $createAssetId);
                @endphp
                @if($selectedAsset)
                    <div class="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:text size="sm" class="text-blue-600 dark:text-blue-400 font-medium">Selected Asset</flux:text>
                                <flux:text variant="strong" class="text-blue-900 dark:text-blue-100">{{ $selectedAsset->asset_code }}</flux:text>
                                <flux:text size="sm" class="text-blue-700 dark:text-blue-300">{{ $selectedAsset->name }}</flux:text>
                            </div>
                            <div class="text-right">
                                <flux:text size="sm" class="text-blue-600 dark:text-blue-400">Current Condition</flux:text>
                                <div class="mt-1">
                                    @switch($selectedAsset->condition)
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
                        </div>
                    </div>
                @endif
            @endif

            {{-- Condition Result --}}
            <div>
                <flux:field>
                    <div class="flex items-center gap-2 mb-3">
                        <flux:icon.clipboard-document-list class="size-4 text-gray-400" />
                        <flux:label>Inspection Result <span class="text-red-500">*</span></flux:label>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all
                            {{ $createCondition === 'baik' 
                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20' 
                                : 'border-zinc-200 dark:border-zinc-700 hover:border-green-300 dark:hover:border-green-700 hover:bg-green-50/50 dark:hover:bg-green-900/10' 
                            }}">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="baik" 
                                class="w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <flux:icon.check-circle class="size-5 text-green-500" />
                                    <flux:text variant="strong" class="text-green-700 dark:text-green-400">Good (Baik)</flux:text>
                                </div>
                                <flux:text size="sm" class="text-zinc-500 mt-1">Asset is in good working condition</flux:text>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all
                            {{ $createCondition === 'perlu_perbaikan' 
                                ? 'border-yellow-500 bg-yellow-50 dark:bg-yellow-900/20' 
                                : 'border-zinc-200 dark:border-zinc-700 hover:border-yellow-300 dark:hover:border-yellow-700 hover:bg-yellow-50/50 dark:hover:bg-yellow-900/10' 
                            }}">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="perlu_perbaikan" 
                                class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <flux:icon.wrench class="size-5 text-yellow-500" />
                                    <flux:text variant="strong" class="text-yellow-700 dark:text-yellow-400">Needs Repair (Perlu Perbaikan)</flux:text>
                                </div>
                                <flux:text size="sm" class="text-zinc-500 mt-1">Asset requires maintenance or repair</flux:text>
                            </div>
                        </label>
                        
                        <label class="flex items-center gap-3 p-4 rounded-lg border-2 cursor-pointer transition-all
                            {{ $createCondition === 'rusak' 
                                ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                                : 'border-zinc-200 dark:border-zinc-700 hover:border-red-300 dark:hover:border-red-700 hover:bg-red-50/50 dark:hover:bg-red-900/10' 
                            }}">
                            <input 
                                type="radio" 
                                wire:model="createCondition" 
                                value="rusak" 
                                class="w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500"
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <flux:icon.x-circle class="size-5 text-red-500" />
                                    <flux:text variant="strong" class="text-red-700 dark:text-red-400">Damaged (Rusak)</flux:text>
                                </div>
                                <flux:text size="sm" class="text-zinc-500 mt-1">Asset is damaged or non-functional</flux:text>
                            </div>
                        </label>
                    </div>
                    <flux:error name="createCondition" />
                </flux:field>
            </div>

            {{-- Description --}}
            <div>
                <flux:field>
                    <div class="flex items-center gap-2 mb-2">
                        <flux:icon.document-text class="size-4 text-gray-400" />
                        <flux:label>Notes <span class="text-zinc-400 text-sm font-normal">(optional)</span></flux:label>
                    </div>
                    <flux:textarea
                        wire:model="createDescription"
                        placeholder="Add any notes about the inspection findings, observations, or recommendations..."
                        rows="4"
                    />
                    <flux:description class="mt-1">Document any specific issues or observations</flux:description>
                    <flux:error name="createDescription" />
                </flux:field>
            </div>

            {{-- Warning --}}
            <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                <div class="flex gap-3">
                    <flux:icon.exclamation-triangle class="size-5 text-amber-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <flux:text size="sm" variant="strong" class="text-amber-800 dark:text-amber-200">Important</flux:text>
                        <flux:text size="sm" class="text-amber-700 dark:text-amber-300 mt-1">
                            This inspection will update the asset's condition field. It will NOT change the asset's status or availability.
                        </flux:text>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-3 justify-end pt-2 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="closeCreateModal">
                    Cancel
                </flux:button>
                <flux:button 
                    type="submit" 
                    variant="primary"
                    icon="check"
                    :disabled="!$createAssetId || !$createCondition"
                >
                    Save Inspection
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
