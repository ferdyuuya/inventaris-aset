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
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Procurements</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage purchase records and track asset origins</flux:subheading>
        </div>
        @if(auth()->user()->isAdmin())
        <flux:modal.trigger name="createProcurement">
            <flux:button variant="primary" icon="plus">
                Add Procurement
            </flux:button>
        </flux:modal.trigger>
        @endif
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            {{-- Search Input --}}
            <div class="flex-1 max-w-md">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search by product, supplier, or invoice..."
                    icon="magnifying-glass"
                    clearable
                    class="text-gray-900 dark:text-white"
                />
            </div>

            {{-- Vertical Separator --}}
            <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            {{-- Sort Dropdown --}}
            <div class="flex flex-wrap gap-3">
                <flux:dropdown position="bottom" align="start">
                    <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                        Sort
                    </flux:button>

                    <flux:menu>
                        <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                        <flux:separator />
                        <flux:menu.item
                            wire:click="toggleSort('procurement_date')"
                            @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'procurement_date'])
                        >
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'procurement_date'])>
                                📅 Date {{ $sortField === 'procurement_date' ? ($sortOrder === 'asc' ? '(Oldest)' : '(Newest)') : '' }}
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="toggleSort('name')"
                            @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name'])
                        >
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name'])>
                                📝 Product Name {{ $sortField === 'name' ? ($sortOrder === 'asc' ? '(A→Z)' : '(Z→A)') : '' }}
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="toggleSort('total_cost')"
                            @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'total_cost'])
                        >
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'total_cost'])>
                                💰 Total Cost {{ $sortField === 'total_cost' ? ($sortOrder === 'asc' ? '(Low→High)' : '(High→Low)') : '' }}
                            </span>
                        </flux:menu.item>
                        <flux:menu.item
                            wire:click="toggleSort('quantity')"
                            @class(['bg-blue-50 dark:bg-blue-900/30' => $sortField === 'quantity'])
                        >
                            <span @class(['font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'quantity'])>
                                📦 Quantity {{ $sortField === 'quantity' ? ($sortOrder === 'asc' ? '(Low→High)' : '(High→Low)') : '' }}
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>

                {{-- Clear Filters Button --}}
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

    {{-- Create Procurement Modal --}}
    <flux:modal name="createProcurement" class="md:w-96" @close="resetForm">
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
                    <x-file-upload
                        wire:model="temporaryDocuments"
                        label="Documents (Invoice, Warranty, etc.)"
                        accept=".pdf"
                        multiple
                        hint="PDF files only • Max 2MB each • Up to 3 files maximum" />
                    
                    @if(count($temporaryDocuments) > 0)
                        <div class="mt-3 space-y-2">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Uploaded files:</p>
                            @foreach($temporaryDocuments as $index => $doc)
                                <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/30 p-2 rounded border border-blue-200 dark:border-blue-700">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        📄 {{ $doc->getClientOriginalName() }}
                                    </span>
                                    <button type="button" 
                                            wire:click="removeDocument({{ $index }})"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @error('documents') <span class="text-red-500 text-xs block mt-2">{{ $message }}</span> @enderror
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
                <flux:button type="submit" variant="primary">Create Procurement</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Procurement Modal --}}
    <flux:modal wire:model.self="showEditModal" class="md:w-96" @close="resetForm">
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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Documents (Invoice, Warranty, etc.)
                    </label>
                    
                    @if(count($documents) > 0)
                        <div class="mb-3 space-y-2">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Current documents:</p>
                            @foreach($documents as $index => $doc)
                                <div class="flex items-center justify-between bg-green-50 dark:bg-green-900/30 p-2 rounded border border-green-200 dark:border-green-700">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        📄 {{ basename($doc) }}
                                    </span>
                                    <button type="button" 
                                            wire:click="removeDocument({{ $index }})"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    <x-file-upload
                        wire:model="temporaryDocuments"
                        accept=".pdf"
                        multiple
                        hint="Add more documents (PDF only • Max 2MB each • {{ 3 - count($documents) - count($temporaryDocuments) }} slot(s) available)" />
                    
                    @if(count($temporaryDocuments) > 0)
                        <div class="mt-3 space-y-2">
                            <p class="text-xs font-medium text-gray-600 dark:text-gray-400">New files:</p>
                            @foreach($temporaryDocuments as $index => $doc)
                                <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/30 p-2 rounded border border-blue-200 dark:border-blue-700">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">
                                        📄 {{ $doc->getClientOriginalName() }}
                                    </span>
                                    <button type="button" 
                                            wire:click="removeDocument({{ $index }})"
                                            class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                    @error('documents') <span class="text-red-500 text-xs block mt-2">{{ $message }}</span> @enderror
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
                                   @if($isEditing) disabled @endif
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
                                   @if($isEditing) disabled @endif
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

    {{-- Confirm Quantity Modal --}}
    <flux:modal wire:model.self="showConfirmQuantityModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Confirm Procurement Creation</flux:heading>
                <flux:text class="mt-2 text-sm">This will generate <strong>{{ $quantity }}</strong> of <strong>{{ $name }}</strong> and reserved at <strong>{{ $location }}</strong> and it can't be undone.</flux:text>
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
                            <strong>Important:</strong> This action cannot be reversed. Please review the quantity and location carefully.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button variant="ghost" wire:click="$toggle('showConfirmQuantityModal')">Cancel</flux:button>
                <flux:button type="button" variant="primary" wire:click="confirmCreateProcurement">Confirm & Create</flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Location Confirmation Modal (Legacy) --}}
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
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">Product</flux:table.column>
                    <flux:table.column>Supplier</flux:table.column>
                    <flux:table.column>Category</flux:table.column>
                    <flux:table.column sortable :sorted="$sortField === 'procurement_date'" :direction="$sortOrder" wire:click="toggleSort('procurement_date')">Date</flux:table.column>
                    <flux:table.column sortable :sorted="$sortField === 'quantity'" :direction="$sortOrder" wire:click="toggleSort('quantity')">Qty</flux:table.column>
                    <flux:table.column sortable :sorted="$sortField === 'total_cost'" :direction="$sortOrder" wire:click="toggleSort('total_cost')">Total Cost</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->procurements as $procurement)
                        <flux:table.row 
                            :key="$procurement->id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="$dispatch('navigate', { url: '{{ route('procurements.detail', $procurement->id) }}' })"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($this->procurements->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div>
                                    <flux:text variant="strong" color="blue">{{ $procurement->name }}</flux:text>
                                    @if($procurement->invoice_number)
                                        <flux:text size="sm" class="text-zinc-500">{{ $procurement->invoice_number }}</flux:text>
                                    @endif
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text>{{ $procurement->supplier->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue" size="sm">
                                    {{ $procurement->category->name }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $procurement->procurement_date->format('d M Y') }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm" variant="soft">{{ $procurement->quantity }}</flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="subtle" size="sm">{{ $procurement->created_at->diffForHumans() }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item 
                                            icon="eye" 
                                            href="{{ route('procurements.detail', $procurement->id) }}"
                                            wire:navigate
                                        >
                                            View Details
                                        </flux:menu.item>
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $procurement->id }})">
                                            Edit
                                        </flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item 
                                            icon="trash" 
                                            variant="danger" 
                                            wire:click="delete({{ $procurement->id }})" 
                                            wire:confirm="Are you sure you want to delete this procurement?"
                                        >
                                            Delete
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="9" class="text-center py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <flux:icon.inbox class="h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" />
                                    <flux:heading size="lg" class="text-zinc-600 dark:text-zinc-400">No procurements found</flux:heading>
                                    <flux:text class="mt-2 text-zinc-500">
                                        @if($search)
                                            Try adjusting your search term.
                                        @else
                                            Get started by creating your first procurement.
                                        @endif
                                    </flux:text>
                                    @if(!$search)
                                        <flux:modal.trigger name="createProcurement">
                                            <flux:button variant="primary" class="mt-4" icon="plus">
                                                Create First Procurement
                                            </flux:button>
                                        </flux:modal.trigger>
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
    @if($this->procurements->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$this->procurements" />
        </div>
    @endif
</div>
