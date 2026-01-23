@php
    use App\Helpers\BadgeColorHelper;
@endphp

<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Assets</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage and track all company assets</flux:subheading>
        </div>
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        {{-- Search Input --}}
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            <div class="flex-1 max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by code or name..."
                    icon="magnifying-glass"
                    clearable
                    class="text-gray-900 dark:text-white"
                />
            </div>

            {{-- Vertical Separator --}}
            <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            {{-- Filter Dropdowns --}}
            <div class="flex flex-wrap gap-3">
                {{-- Location Filter --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="funnel"
                        :badge="$filterLocation ? '1' : null"
                    >
                        Location
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('filterLocation', null)"
                            :class="$filterLocation === null ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                        >
                            <span :class="$filterLocation === null ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                                All Locations
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        @foreach($locations as $location)
                            <flux:menu.item
                                wire:click="$set('filterLocation', {{ $location->id }})"
                                @class([
                                    'bg-blue-50 dark:bg-blue-900/30' => $filterLocation === $location->id,
                                ])
                            >
                                <span @class([
                                    'font-semibold text-blue-600 dark:text-blue-400' => $filterLocation === $location->id,
                                ])>
                                    {{ $location->name }}
                                </span>
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

                {{-- Category Filter --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button
                        variant="ghost"
                        size="sm"
                        icon="funnel"
                        :badge="$filterCategory ? '1' : null"
                    >
                        Category
                    </flux:button>

                    <flux:menu>
                        <flux:menu.item
                            wire:click="$set('filterCategory', null)"
                            :class="$filterCategory === null ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                        >
                            <span :class="$filterCategory === null ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                                All Categories
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        @foreach($categories as $category)
                            <flux:menu.item
                                wire:click="$set('filterCategory', {{ $category->id }})"
                                @class([
                                    'bg-blue-50 dark:bg-blue-900/30' => $filterCategory === $category->id,
                                ])
                            >
                                <span @class([
                                    'font-semibold text-blue-600 dark:text-blue-400' => $filterCategory === $category->id,
                                ])>
                                    {{ $category->name }}
                                </span>
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

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
                            wire:click="$set('filterStatus', null)"
                            :class="$filterStatus === null ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                        >
                            <span :class="$filterStatus === null ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                                All Statuses
                            </span>
                        </flux:menu.item>
                        <flux:separator />
                        @foreach($statuses as $statusValue => $statusLabel)
                            <flux:menu.item
                                wire:click="$set('filterStatus', '{{ $statusValue }}')"
                                @class([
                                    'bg-blue-50 dark:bg-blue-900/30' => $filterStatus === $statusValue,
                                ])
                            >
                                <span @class([
                                    'font-semibold text-blue-600 dark:text-blue-400' => $filterStatus === $statusValue,
                                ])>
                                    {{ $statusLabel }}
                                </span>
                            </flux:menu.item>
                        @endforeach
                    </flux:menu>
                </flux:dropdown>

                {{-- Sorting Dropdown (aligned with filters) --}}
                <flux:dropdown position="bottom" align="start">
                    <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                        Sort
                    </flux:button>

                    <flux:menu>
                        <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                        <flux:separator />
                        
                        {{-- Newest --}}
                        <flux:menu.item
                            wire:click="setSortField('created_at', 'desc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'desc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'desc',
                            ])>
                                Newest
                            </span>
                        </flux:menu.item>

                        {{-- Oldest --}}
                        <flux:menu.item
                            wire:click="setSortField('created_at', 'asc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'asc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'asc',
                            ])>
                                Oldest
                            </span>
                        </flux:menu.item>

                        <flux:separator />

                        {{-- A-Z --}}
                        <flux:menu.item
                            wire:click="setSortField('name', 'asc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'asc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'asc',
                            ])>
                                A–Z
                            </span>
                        </flux:menu.item>

                        {{-- Z-A --}}
                        <flux:menu.item
                            wire:click="setSortField('name', 'desc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'desc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'desc',
                            ])>
                                Z–A
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters Button --}}
                @if($hasActiveFilters || $search)
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

        {{-- Active Filters Summary (optional) --}}
        @if($hasActiveFilters || $search)
            <div class="flex flex-wrap gap-2 items-center text-sm">
                <flux:text class="text-gray-600 dark:text-gray-400">Active filters:</flux:text>
                @if($search)
                    <flux:badge color="blue" size="sm">
                        Search: <strong>{{ $search }}</strong>
                    </flux:badge>
                @endif
                @if($filterLocation)
                    <flux:badge color="blue" size="sm">
                        Location: <strong>{{ $locations->find($filterLocation)?->name }}</strong>
                    </flux:badge>
                @endif
                @if($filterCategory)
                    <flux:badge color="blue" size="sm">
                        Category: <strong>{{ $categories->find($filterCategory)?->name }}</strong>
                    </flux:badge>
                @endif
                @if($filterStatus)
                    <flux:badge color="blue" size="sm">
                        Status: <strong>{{ $statuses[$filterStatus] ?? $filterStatus }}</strong>
                    </flux:badge>
                @endif
            </div>
        @endif
    </div>

    <flux:separator />

    {{-- Assets Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($assets->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Category</flux:table.column>
                    <flux:table.column>Location</flux:table.column>
                    <flux:table.column>Status</flux:table.column>
                    <flux:table.column>Condition</flux:table.column>
                    <flux:table.column>Last Updated</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($assets as $asset)
                        <flux:table.row 
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="$dispatch('navigate', { url: '{{ route('assets.show', $asset) }}' })"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($assets->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong" color="blue">{{ $asset->asset_code }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $asset->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge size="sm" :color="BadgeColorHelper::getCategoryColor($asset->category)" variant="solid">
                                    {{ $asset->category->name ?? '-' }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.map-pin class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $asset->location->name ?? '-' }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                @switch($asset->status)
                                    @case('aktif')
                                        <flux:badge color="green" size="sm">
                                            <flux:icon.check-circle class="size-3 mr-1" />
                                            Active
                                        </flux:badge>
                                        @break
                                    @case('dipinjam')
                                        <flux:badge color="purple" size="sm">
                                            <flux:icon.arrow-right-circle class="size-3 mr-1" />
                                            Borrowed
                                        </flux:badge>
                                        @break
                                    @case('dipelihara')
                                        <flux:badge color="yellow" size="sm">
                                            <flux:icon.wrench-screwdriver class="size-3 mr-1" />
                                            Maintenance
                                        </flux:badge>
                                        @break
                                    @case('dihapuskan')
                                        <flux:badge color="zinc" size="sm">
                                            <flux:icon.archive-box-x-mark class="size-3 mr-1" />
                                            Disposed
                                        </flux:badge>
                                        @break
                                    @case('nonaktif')
                                        <flux:badge color="red" size="sm" variant="soft">
                                            <flux:icon.x-circle class="size-3 mr-1" />
                                            Inactive
                                        </flux:badge>
                                        @break
                                    @default
                                        <flux:badge color="zinc" size="sm" variant="soft">
                                            {{ ucfirst($asset->status) }}
                                        </flux:badge>
                                @endswitch
                            </flux:table.cell>
                            <flux:table.cell>
                                @switch($asset->condition)
                                    @case('baik')
                                        <flux:badge color="emerald" size="sm" variant="soft">
                                            Good
                                        </flux:badge>
                                        @break
                                    @case('rusak')
                                        <flux:badge color="red" size="sm" variant="soft">
                                            Damaged
                                        </flux:badge>
                                        @break
                                    @case('perlu_perbaikan')
                                        <flux:badge color="orange" size="sm" variant="soft">
                                            Needs Repair
                                        </flux:badge>
                                        @break
                                    @default
                                        <flux:badge color="zinc" size="sm" variant="soft">
                                            {{ $asset->condition ? ucfirst(str_replace('_', ' ', $asset->condition)) : '-' }}
                                        </flux:badge>
                                @endswitch
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $asset->updated_at->format('d M Y, H:i') }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />

                                    <flux:menu>
                                        <flux:menu.item
                                            href="{{ route('assets.show', $asset) }}"
                                            icon="eye"
                                            wire:navigate
                                        >
                                            View
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-12">
                <flux:icon.inbox class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No assets found</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    @if($hasActiveFilters || $search)
                        Try adjusting your filters or search term.
                    @else
                        Get started by creating your first asset.
                    @endif
                </flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($assets->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$assets" />
        </div>
    @endif
</div>
