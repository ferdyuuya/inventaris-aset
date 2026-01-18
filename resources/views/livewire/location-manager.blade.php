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
    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Location Management</h1>
            <flux:modal.trigger name="createLocation" wire:click="showCreateForm">
                <flux:button variant="primary">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Location
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search locations by name or description..."
                       clearable />
        </div>
    </div>

    {{-- Locations Table --}}
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
        <div class="min-w-full">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Responsible Employee</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($locations as $location)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $location->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">{{ $location->description ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if($location->responsibleEmployee)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ $location->responsibleEmployee->name }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No employee</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $location->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <flux:dropdown position="left" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="showEditForm({{ $location->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $location->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No locations found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($locations->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <flux:pagination :paginator="$locations" />
        </div>
        @endif
    </div>

    {{-- Create Location Modal --}}
    <flux:modal name="createLocation" class="md:w-96">
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
    <flux:modal name="editLocation" class="md:w-96">
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
