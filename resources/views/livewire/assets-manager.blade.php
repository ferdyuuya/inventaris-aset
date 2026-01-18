<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Assets</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage and track all company assets</flux:subheading>
        </div>
    </div>

    <flux:separator />

    {{-- Search Bar --}}
    <div class="max-w-md">
        <flux:input
            wire:model.live="search"
            type="text"
            placeholder="Search by code or name..."
            icon="magnifying-glass"
            clearable
            class="text-gray-900 dark:text-white"
        />
    </div>

    {{-- Assets Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'asset_code'" :direction="$sortOrder" wire:click="toggleSort('asset_code')">Code</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">Name</flux:table.column>
                <flux:table.column>Category</flux:table.column>
                <flux:table.column>Location</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($assets as $asset)
                    <flux:table.row>
                        <flux:table.cell>
                            <flux:text size="sm" variant="subtle">{{ $loop->iteration }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text variant="strong" color="blue" class="font-mono">{{ $asset->asset_code }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text variant="strong">{{ $asset->name }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue" variant="solid">
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
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} results
        </div>
        {{ $assets->links() }}
    </div>
</div>
