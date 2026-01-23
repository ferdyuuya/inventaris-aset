<div class="space-y-6">
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
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
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Locations</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage asset storage locations</flux:subheading>
        </div>
        <flux:modal.trigger name="createLocation" wire:click="showCreateForm">
            <flux:button variant="primary" icon="plus">
                Add Location
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator />

    {{-- Search and Sort Bar --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
        {{-- Search Input --}}
        <div class="flex-1 max-w-md">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search locations by name or description..."
                       clearable />
        </div>

        {{-- Vertical Separator --}}
        <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

        {{-- Sort Dropdown --}}
        <div class="flex flex-wrap gap-3">
            <flux:dropdown position="bottom" align="start">
                <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                    Sort: {{ $sortField === 'name' ? ($sortOrder === 'asc' ? 'A–Z' : 'Z–A') : ($sortOrder === 'desc' ? 'Newest' : 'Oldest') }}
                </flux:button>
                <flux:menu>
                    <flux:menu.item wire:click="$set('sortField', 'created_at'); $set('sortOrder', 'desc')" @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'desc'])>
                        <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'desc'])>Newest</span>
                    </flux:menu.item>
                    <flux:menu.item wire:click="$set('sortField', 'created_at'); $set('sortOrder', 'asc')" @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'asc'])>
                        <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'asc'])>Oldest</span>
                    </flux:menu.item>
                    <flux:separator />
                    <flux:menu.item wire:click="$set('sortField', 'name'); $set('sortOrder', 'asc')" @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'asc'])>
                        <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'asc'])>A–Z</span>
                    </flux:menu.item>
                    <flux:menu.item wire:click="$set('sortField', 'name'); $set('sortOrder', 'desc')" @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'desc'])>
                        <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'desc'])>Z–A</span>
                    </flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            {{-- Clear Search --}}
            @if($search)
                <flux:button variant="ghost" size="sm" icon="x-mark" wire:click="$set('search', '')">
                    Clear
                </flux:button>
            @endif
        </div>
    </div>

    <flux:separator />

    {{-- Locations Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($locations->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Responsible Employee</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($locations as $location)
                        <flux:table.row 
                            :key="$location->id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="showEditForm({{ $location->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($locations->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.map-pin class="size-4 text-gray-400" />
                                    <flux:text variant="strong">{{ $location->name }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="text-zinc-500">{{ Str::limit($location->description, 40) ?? '-' }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($location->responsibleEmployee)
                                    <flux:badge color="green" size="sm">
                                        <flux:icon.user class="size-3 mr-1" />
                                        {{ $location->responsibleEmployee->name }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" variant="outline">No employee</flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $location->created_at->format('d M Y, H:i') }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="showEditForm({{ $location->id }})">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $location->id }})">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="p-12 text-center">
                <flux:icon.map-pin class="size-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <flux:heading size="lg" class="text-gray-900 dark:text-white">No locations found</flux:heading>
                <flux:text class="text-gray-500 dark:text-gray-400 mt-1">Get started by creating a new location.</flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($locations->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$locations" />
        </div>
    @endif

    {{-- Create Location Modal --}}
    <flux:modal name="createLocation" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Create New Location</flux:heading>
                <flux:text class="mt-2 text-sm">Enter the location information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="map-pin" label="Location Name" description="The name of the location" placeholder="Enter location name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="description" icon="document-text" label="Description" description="Optional description for this location" placeholder="Enter description" />
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="responsible_employee_id" label="Responsible Employee" description="Assign an employee to manage this location (optional)" placeholder="Select an employee">
                        <flux:select.option value="">-- None --</flux:select.option>
                        @foreach($employees as $employee)
                            <flux:select.option value="{{ $employee->id }}">{{ $employee->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('responsible_employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create Location</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Location Modal --}}
    <flux:modal name="editLocation" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Location</flux:heading>
                <flux:text class="mt-2 text-sm">Update the location information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="map-pin" label="Location Name" description="The name of the location" placeholder="Enter location name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="description" icon="document-text" label="Description" description="Optional description for this location" placeholder="Enter description" />
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="responsible_employee_id" label="Responsible Employee" description="Assign an employee to manage this location (optional)" placeholder="Select an employee">
                        <flux:select.option value="">-- None --</flux:select.option>
                        @foreach($employees as $employee)
                            <flux:select.option value="{{ $employee->id }}">{{ $employee->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('responsible_employee_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Location</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="deleteLocation" class="md:w-96">
        @if($locationToDelete)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Location</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete <strong>{{ $locationToDelete->name }}</strong>? This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDelete" variant="danger">Delete Location</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>
