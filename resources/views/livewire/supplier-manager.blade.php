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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Supplier Management</h1>
            <flux:modal.trigger name="createSupplier" wire:click="showCreateForm">
                <flux:button variant="primary">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Supplier
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search suppliers by name, email, or phone..."
                       clearable />
        </div>
    </div>

    {{-- Suppliers Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">
                Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'email'" :direction="$sortOrder" wire:click="toggleSort('email')">
                Email
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'phone'" :direction="$sortOrder" wire:click="toggleSort('phone')">
                Phone
            </flux:table.column>
            <flux:table.column>
                Address
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortOrder" wire:click="toggleSort('created_at')">
                Created
            </flux:table.column>
            <flux:table.column>
                Actions
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse($suppliers as $supplier)
                <flux:table.row :key="$supplier->id">
                    <flux:table.cell class="font-medium">
                        {{ $supplier->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:text size="sm">
                            {{ $supplier->email ?? '-' }}
                        </flux:text>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:text size="sm">
                            {{ $supplier->phone ?? '-' }}
                        </flux:text>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:text size="sm">
                            {{ $supplier->address ?? '-' }}
                        </flux:text>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:text size="sm">
                            {{ $supplier->created_at->format('M d, Y') }}
                        </flux:text>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="left" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="showEditForm({{ $supplier->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $supplier->id }})">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                        No suppliers found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Pagination --}}
    @if($suppliers->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$suppliers" />
        </div>
    @endif

    {{-- Create Supplier Modal --}}
    <flux:modal name="createSupplier" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Create New Supplier</flux:heading>
                <flux:text class="mt-2 text-sm">Enter the supplier's information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="building-storefront" label="Supplier Name" description="The supplier's business name" placeholder="Enter supplier name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="email" icon="envelope" label="Email" description="Supplier email address" type="email" placeholder="Enter email address" />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="phone" icon="phone" label="Phone" description="Supplier phone number" placeholder="Enter phone number" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="address" icon="map-pin" label="Address" description="Supplier's physical address" placeholder="Enter address" />
                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create Supplier</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Supplier Modal --}}
    <flux:modal name="editSupplier" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Supplier</flux:heading>
                <flux:text class="mt-2 text-sm">Update the supplier's information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="building-storefront" label="Supplier Name" description="The supplier's business name" placeholder="Enter supplier name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="email" icon="envelope" label="Email" description="Supplier email address" type="email" placeholder="Enter email address" />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="phone" icon="phone" label="Phone" description="Supplier phone number" placeholder="Enter phone number" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="address" icon="map-pin" label="Address" description="Supplier's physical address" placeholder="Enter address" />
                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Supplier</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="deleteSupplier" class="md:w-96">
        @if($supplierToDelete)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Supplier</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete <strong>{{ $supplierToDelete->name }}</strong>? This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDelete" variant="danger">Delete Supplier</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>
