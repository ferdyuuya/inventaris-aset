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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Procurement Management</h1>
            <flux:modal.trigger name="createProcurement">
                <flux:button variant="primary" wire:click="showCreateForm">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Procurement
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search procurements by product name, supplier, or invoice..."
                       clearable />
        </div>
    </div>

    {{-- Create Procurement Modal --}}
    <flux:modal name="createProcurement" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Procurement</flux:heading>
                <flux:text class="mt-2 text-sm">Enter procurement details including product, supplier and cost information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" 
                               icon="tag"
                               label="Product Name" 
                               description="Name of the product being procured"
                               placeholder="e.g., Office Chairs, Laptop, Monitor"
                               required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="asset_category_id" 
                               label="Category" 
                               description="Product category"
                               placeholder="Choose category"
                               required>
                        <flux:select.option value="">-- Select Category --</flux:select.option>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('asset_category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="location_id" 
                               label="Location" 
                               description="Storage location (cannot be changed after creation)"
                               placeholder="Choose location"
                               required>
                        <flux:select.option value="">-- Select Location --</flux:select.option>
                        @foreach($locations as $location)
                            <flux:select.option value="{{ $location->id }}">{{ $location->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('location_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="supplier_id" 
                               label="Supplier" 
                               description="Select supplier for this procurement"
                               placeholder="Choose supplier"
                               required>
                        <flux:select.option value="">-- Select Supplier --</flux:select.option>
                        @foreach($suppliers as $supplier)
                            <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="procurement_date" 
                               icon="calendar"
                               label="Procurement Date" 
                               type="date"
                               description="Date of procurement"
                               required />
                    @error('procurement_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="invoice_number" 
                               icon="document"
                               label="Invoice Number" 
                               description="Invoice number (optional)"
                               placeholder="e.g., INV-2026-001" />
                    @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:heading>Pricing Information</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input wire:model.live="quantity" 
                                   icon="hashtag"
                                   label="Quantity" 
                                   type="number"
                                   min="0"
                                   step="1"
                                   description="Number of items"
                                   placeholder="0"
                                   required />
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input wire:model.live="unit_price" 
                                   icon="currency-dollar"
                                   label="Unit Price (Rp)" 
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   description="Price per unit"
                                   placeholder="0"
                                   required />
                        @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <flux:input wire:model="total_cost" 
                               icon="banknotes"
                               label="Total Cost (Rp)" 
                               type="number"
                               min="0"
                               step="0.01"
                               description="Automatically calculated: Quantity × Unit Price"
                               placeholder="0"
                               readonly
                               variant="filled" />
                    @error('total_cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" wire:click="save">Create Procurement</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Procurement Modal --}}
    <flux:modal name="editProcurement" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Procurement</flux:heading>
                <flux:text class="mt-2 text-sm">Update procurement details.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" 
                               icon="tag"
                               label="Product Name" 
                               description="Name of the product being procured"
                               placeholder="e.g., Office Chairs, Laptop, Monitor"
                               required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="asset_category_id" 
                               label="Category" 
                               description="Product category"
                               placeholder="Choose category"
                               required>
                        <flux:select.option value="">-- Select Category --</flux:select.option>
                        @foreach($categories as $category)
                            <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('asset_category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    @php
                        $location = $locations->firstWhere('id', $location_id);
                    @endphp
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Location</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-600 rounded-lg border border-gray-300 dark:border-gray-600">
                        <p class="text-gray-900 dark:text-white text-sm">{{ $location ? $location->name : 'Not set' }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-xs mt-1">Cannot be changed after creation</p>
                    </div>
                </div>

                <div>
                    <flux:select wire:model="supplier_id" 
                               label="Supplier" 
                               description="Select supplier for this procurement"
                               placeholder="Choose supplier"
                               required>
                        <flux:select.option value="">-- Select Supplier --</flux:select.option>
                        @foreach($suppliers as $supplier)
                            <flux:select.option value="{{ $supplier->id }}">{{ $supplier->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="procurement_date" 
                               icon="calendar"
                               label="Procurement Date" 
                               type="date"
                               description="Date of procurement"
                               required />
                    @error('procurement_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="invoice_number" 
                               icon="document"
                               label="Invoice Number" 
                               description="Invoice number (optional)"
                               placeholder="e.g., INV-2026-001" />
                    @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:heading>Pricing Information</flux:heading>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:input wire:model.live="quantity" 
                                   icon="hashtag"
                                   label="Quantity" 
                                   type="number"
                                   min="0"
                                   step="1"
                                   description="Number of items"
                                   placeholder="0"
                                   required />
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input wire:model.live="unit_price" 
                                   icon="currency-dollar"
                                   label="Unit Price (Rp)" 
                                   type="number"
                                   min="0"
                                   step="0.01"
                                   description="Price per unit"
                                   placeholder="0"
                                   required />
                        @error('unit_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <flux:input wire:model="total_cost" 
                               icon="banknotes"
                               label="Total Cost (Rp)" 
                               type="number"
                               min="0"
                               step="0.01"
                               description="Automatically calculated: Quantity × Unit Price"
                               placeholder="0"
                               readonly
                               variant="filled" />
                    @error('total_cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Procurement</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Location Confirmation Modal --}}
    <flux:modal wire:model.self="showConfirmLocationModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Confirm Procurement Creation</flux:heading>
                <flux:text class="mt-2 text-sm">Please review the location before confirming.</flux:text>
            </div>

            <div class="bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            <strong>Important:</strong> Location cannot be changed after this procurement is created.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="$toggle('showConfirmLocationModal')">Cancel</flux:button>
                <flux:button type="submit" variant="primary" wire:click="confirmCreateProcurement">Confirm & Create</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Procurements Table --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">Product</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'asset_category_id'" :direction="$sortOrder" wire:click="toggleSort('asset_category_id')">Category</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'supplier_id'" :direction="$sortOrder" wire:click="toggleSort('supplier_id')">Supplier</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'invoice_number'" :direction="$sortOrder" wire:click="toggleSort('invoice_number')">Invoice</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'quantity'" :direction="$sortOrder" wire:click="toggleSort('quantity')">Quantity</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'unit_price'" :direction="$sortOrder" wire:click="toggleSort('unit_price')">Unit Price</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'total_cost'" :direction="$sortOrder" wire:click="toggleSort('total_cost')">Total Cost</flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'procurement_date'" :direction="$sortOrder" wire:click="toggleSort('procurement_date')">Date</flux:table.column>
            <flux:table.column>Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->procurements as $procurement)
                <flux:table.row :key="$procurement->id">
                    <flux:table.cell class="font-medium">
                        {{ $procurement->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="blue" inset="top bottom">
                            {{ $procurement->category->name }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $procurement->supplier->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $procurement->invoice_number ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $procurement->quantity }}
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        Rp {{ number_format($procurement->unit_price, 0, ',', '.') }}
                    </flux:table.cell>
                    <flux:table.cell variant="strong">
                        Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap">
                        {{ $procurement->procurement_date->format('d M Y') }}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="left" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset />
                            <flux:menu>
                                <flux:menu.item icon="pencil" wire:click="edit({{ $procurement->id }})">Edit</flux:menu.item>
                                <flux:menu.separator />
                                <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $procurement->id }})" wire:confirm="Are you sure you want to delete this procurement?">Delete</flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                        No procurements found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    {{-- Pagination --}}
    @if($this->procurements->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$this->procurements" />
        </div>
    @endif
</div>
