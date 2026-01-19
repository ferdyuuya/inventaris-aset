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
                    wire:model.live="search"
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
        {{-- Table Header with Sort Options --}}
        <div class="mb-4 flex items-center justify-between">
            <flux:text size="sm" class="text-gray-600 dark:text-gray-400">
                Sorted by: <strong>{{ ucwords(str_replace('_', ' ', $sortField)) }}</strong> ({{ $sortOrder === 'asc' ? 'A→Z' : 'Z→A' }})
            </flux:text>
            
            <flux:dropdown position="bottom" align="end">
                <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                    Change Sort
                </flux:button>

                <flux:menu>
                    <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                    <flux:separator />
                    
                    {{-- Created Date (newest first) --}}
                    <flux:menu.item
                        wire:click="setSortField('created_at', 'desc')"
                        :class="$sortField === 'created_at' && $sortOrder === 'desc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'created_at' && $sortOrder === 'desc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            📅 Created Date (Newest)
                        </span>
                    </flux:menu.item>

                    {{-- Created Date (oldest first) --}}
                    <flux:menu.item
                        wire:click="setSortField('created_at', 'asc')"
                        :class="$sortField === 'created_at' && $sortOrder === 'asc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'created_at' && $sortOrder === 'asc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            📅 Created Date (Oldest)
                        </span>
                    </flux:menu.item>

                    <flux:separator />

                    {{-- Asset Code A-Z --}}
                    <flux:menu.item
                        wire:click="setSortField('asset_code', 'asc')"
                        :class="$sortField === 'asset_code' && $sortOrder === 'asc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'asset_code' && $sortOrder === 'asc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            🔤 Asset Code (A→Z)
                        </span>
                    </flux:menu.item>

                    {{-- Asset Code Z-A --}}
                    <flux:menu.item
                        wire:click="setSortField('asset_code', 'desc')"
                        :class="$sortField === 'asset_code' && $sortOrder === 'desc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'asset_code' && $sortOrder === 'desc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            🔤 Asset Code (Z→A)
                        </span>
                    </flux:menu.item>

                    <flux:separator />

                    {{-- Asset Name A-Z --}}
                    <flux:menu.item
                        wire:click="setSortField('name', 'asc')"
                        :class="$sortField === 'name' && $sortOrder === 'asc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'name' && $sortOrder === 'asc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            📝 Asset Name (A→Z)
                        </span>
                    </flux:menu.item>

                    {{-- Asset Name Z-A --}}
                    <flux:menu.item
                        wire:click="setSortField('name', 'desc')"
                        :class="$sortField === 'name' && $sortOrder === 'desc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'name' && $sortOrder === 'desc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            📝 Asset Name (Z→A)
                        </span>
                    </flux:menu.item>

                    <flux:separator />

                    {{-- Purchase Date (Newest) --}}
                    <flux:menu.item
                        wire:click="setSortField('purchase_date', 'desc')"
                        :class="$sortField === 'purchase_date' && $sortOrder === 'desc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'purchase_date' && $sortOrder === 'desc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            💰 Purchase Date (Newest)
                        </span>
                    </flux:menu.item>

                    {{-- Purchase Date (Oldest) --}}
                    <flux:menu.item
                        wire:click="setSortField('purchase_date', 'asc')"
                        :class="$sortField === 'purchase_date' && $sortOrder === 'asc' ? 'bg-blue-50 dark:bg-blue-900/30' : ''"
                    >
                        <span :class="$sortField === 'purchase_date' && $sortOrder === 'asc' ? 'font-semibold text-blue-600 dark:text-blue-400' : ''">
                            💰 Purchase Date (Oldest)
                        </span>
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'asset_code'" :direction="$sortOrder" wire:click="toggleSort('asset_code')">Code</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">Name</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Last updated</flux:table.columns>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($assets as $asset)
                    <flux:table.row>
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
                                <flux:icon.map-pin class="h-4 w-4 text-blue-500 dark:text-blue-400 flex-shrink-0" />
                                <flux:text class="text-gray-700 dark:text-gray-300">{{ $asset->location->name ?? '-' }}</flux:text>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell>
                            @switch($asset->status)
                                @case('aktif')
                                    <flux:badge color="success" inset="top bottom">Active</flux:badge>
                                    @break
                                @case('dipinjam')
                                    <flux:badge color="warning" inset="top bottom">Borrowed</flux:badge>
                                    @break
                                @case('dipelihara')
                                    <flux:badge color="info" inset="top bottom">Maintenance</flux:badge>
                                    @break
                                @case('nonaktif')
                                    <flux:badge color="error" inset="top bottom">Inactive</flux:badge>
                                    @break
                            @endswitch
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text variant="subtle">
                                {{ $asset->updated_at->diffForHumans() }}
                            </flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

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
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <div class="flex flex-col items-center justify-center">
                                <flux:icon.inbox class="h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                <flux:text variant="subtle">No assets found</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if($assets->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$assets" />
        </div>
    @endif
</div>
