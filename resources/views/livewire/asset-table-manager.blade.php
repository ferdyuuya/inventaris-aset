<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.check-circle class="h-5 w-5 text-green-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Asset List</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    View all assets in the system • Assets are created from procurement records
                </p>
            </div>
            <div>
                <flux:button href="{{ route('assets.summary') }}" variant="ghost" icon="chart-bar">
                    Summary
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <div class="flex flex-col space-y-4 sm:flex-row sm:space-x-4 sm:space-y-0">
        {{-- Search --}}
        <div class="flex-1">
            <flux:input 
                wire:model.live.debounce.300ms="search" 
                icon="magnifying-glass"
                placeholder="Search by asset code or name..."
                clearable
            />
        </div>

        {{-- Category Filter --}}
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filterCategory" placeholder="All Categories">
                <flux:select.option value="">All Categories</flux:select.option>
                @foreach($categories as $category)
                    <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Status Filter --}}
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filterStatus" placeholder="All Status">
                <flux:select.option value="">All Status</flux:select.option>
                <flux:select.option value="aktif">Active</flux:select.option>
                <flux:select.option value="dipinjam">Borrowed</flux:select.option>
                <flux:select.option value="dipelihara">Maintenance</flux:select.option>
            </flux:select>
        </div>

        {{-- Location Filter --}}
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filterLocation" placeholder="All Locations">
                <flux:select.option value="">All Locations</flux:select.option>
                @foreach($locations as $location)
                    <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Reset Filters --}}
        <div>
            <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">
                Reset
            </flux:button>
        </div>
    </div>

    {{-- Results Info --}}
    @if($assets->total() > 0)
        <div class="flex items-center justify-between">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} assets
            </p>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-500 dark:text-gray-400">Per page:</span>
                <flux:select wire:model.live="perPage" class="w-20">
                    <flux:select.option value="10">10</flux:select.option>
                    <flux:select.option value="25">25</flux:select.option>
                    <flux:select.option value="50">50</flux:select.option>
                    <flux:select.option value="100">100</flux:select.option>
                </flux:select>
            </div>
        </div>
    @endif

    {{-- Asset Table --}}
    <flux:table :paginate="$assets">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortField === 'asset_code'" :direction="$sortOrder" wire:click="toggleSort('asset_code')">
                Asset Code
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">
                Asset Name
            </flux:table.column>
            <flux:table.column>
                Category
            </flux:table.column>
            <flux:table.column>
                Current Location
            </flux:table.column>
            <flux:table.column>
                Status
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'purchase_price'" :direction="$sortOrder" wire:click="toggleSort('purchase_price')">
                Purchase Price
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'purchase_date'" :direction="$sortOrder" wire:click="toggleSort('purchase_date')">
                Purchase Date
            </flux:table.column>
            <flux:table.column>
                Actions
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($assets as $asset)
                <flux:table.row :key="$asset->id" wire:click="$dispatch('viewAsset', { id: {{ $asset->id }} })" class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50">
                    <flux:table.cell class="font-mono font-medium text-blue-600 dark:text-blue-400">
                        {{ $asset->asset_code }}
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        {{ $asset->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc" inset="top bottom">
                            {{ $asset->category?->name ?? 'N/A' }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="flex items-center">
                            <flux:icon.map-pin class="h-4 w-4 mr-1 text-gray-400" />
                            {{ $asset->location?->name ?? 'N/A' }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        @switch($asset->status)
                            @case('aktif')
                                <flux:badge size="sm" color="green" inset="top bottom">
                                    <flux:icon.check-circle class="h-3 w-3 mr-1" />
                                    Active
                                </flux:badge>
                                @break
                            @case('dipinjam')
                                <flux:badge size="sm" color="purple" inset="top bottom">
                                    <flux:icon.arrow-right-circle class="h-3 w-3 mr-1" />
                                    Borrowed
                                </flux:badge>
                                @break
                            @case('dipelihara')
                                <flux:badge size="sm" color="yellow" inset="top bottom">
                                    <flux:icon.wrench-screwdriver class="h-3 w-3 mr-1" />
                                    Maintenance
                                </flux:badge>
                                @break
                            @case('nonaktif')
                                <flux:badge size="sm" color="red" inset="top bottom">
                                    <flux:icon.x-circle class="h-3 w-3 mr-1" />
                                    Inactive
                                </flux:badge>
                                @break
                            @default
                                <flux:badge size="sm" color="zinc" inset="top bottom">
                                    {{ $asset->status }}
                                </flux:badge>
                        @endswitch
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $asset->purchase_date?->format('d M Y') ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button 
                            href="{{ route('assets.show', $asset) }}" 
                            variant="ghost" 
                            size="sm" 
                            icon="eye"
                            wire:click.stop
                        >
                            View
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <flux:icon.archive-box-x-mark class="h-12 w-12 text-gray-400 mb-4" />
                            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">
                                No assets found
                            </p>
                            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                                @if($search || $filterCategory || $filterStatus || $filterLocation)
                                    Try adjusting your search or filter criteria
                                @else
                                    Assets are generated from procurement records
                                @endif
                            </p>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Notice Banner --}}
    <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <flux:icon.information-circle class="h-5 w-5 text-blue-500" />
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                    Read-Only Asset List
                </h3>
                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                    <p>
                        Assets cannot be directly created, edited, or deleted from this page.
                        Assets are automatically generated from approved procurement records.
                        Click on an asset to view its details and perform lifecycle actions.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
