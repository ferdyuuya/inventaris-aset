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

    @if (session()->has('error'))
        <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Button --}}
    <div class="flex justify-end mb-4">
        <flux:button variant="primary" wire:click="openEditModal" icon="pencil">
            Edit Procurement
        </flux:button>
    </div>

    {{-- Procurement Information Card --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Left Column --}}
        <div class="space-y-4">
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Procurement Information</h2>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Product Name</label>
                    <p class="mt-1 text-gray-900 dark:text-white font-medium">{{ $procurement->name }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                    <div class="mt-1">
                        <flux:badge color="blue" inset="top bottom">
                            {{ $procurement->category->name }}
                        </flux:badge>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $procurement->supplier->name }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $procurement->location->name }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Procurement Date</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $procurement->procurement_date->format('d F Y') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Number</label>
                    <p class="mt-1 text-gray-900 dark:text-white">{{ $procurement->invoice_number ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-4">
            {{-- Pricing Card --}}
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pricing Information</h2>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                    <p class="mt-1 text-gray-900 dark:text-white font-medium text-lg">{{ $procurement->quantity }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit Price (Rp)</label>
                    <p class="mt-1 text-gray-900 dark:text-white font-medium text-lg">
                        Rp {{ number_format($procurement->unit_price, 0, ',', '.') }}
                    </p>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Cost (Rp)</label>
                    <p class="mt-1 text-gray-900 dark:text-white font-bold text-2xl text-blue-600 dark:text-blue-400">
                        Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Documents Card --}}
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Documents</h2>

                @if(count($documents) > 0)
                    <div class="space-y-2">
                        @foreach($documents as $doc)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M4 3a2 2 0 012-2h5.5a1 1 0 01.82.4l2.5 3.25H14a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V3z" />
                                    </svg>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ basename($doc) }}</span>
                                </div>
                                <a href="{{ Storage::disk('public')->url($doc) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                                    View
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No documents attached.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Edit Procurement Modal --}}
    <flux:modal wire:model.self="showEditModal" class="md:w-96" @close="closeEditModal">
        <form wire:submit="updateProcurement" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Procurement</flux:heading>
                <flux:text class="mt-2 text-sm">Update procurement details. Some fields are locked to maintain data integrity.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                {{-- Editable Fields --}}
                <div>
                    <flux:input wire:model="name" 
                               icon="tag"
                               label="Product Name" 
                               description="Name of the product"
                               placeholder="e.g., Office Chairs, Laptop, Monitor"
                               required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
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
                               description="Invoice or receipt number"
                               placeholder="e.g., INV-2024-001"
                               required />
                    @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Locked Fields (Read-only) --}}
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-4 border border-gray-200 dark:border-gray-600">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Locked Fields</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">These fields cannot be changed after procurement creation to maintain data integrity.</p>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
                        <div class="mt-1 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                            <p class="text-gray-900 dark:text-white text-sm">{{ $procurement->category->name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Location</label>
                        <div class="mt-1 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                            <p class="text-gray-900 dark:text-white text-sm">{{ $procurement->location->name }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Quantity</label>
                        <div class="mt-1 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                            <p class="text-gray-900 dark:text-white text-sm font-medium">{{ $quantity }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Unit Price (Rp)</label>
                        <div class="mt-1 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                            <p class="text-gray-900 dark:text-white text-sm font-medium">Rp {{ number_format($unit_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Cost (Rp)</label>
                        <div class="mt-1 px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600">
                            <p class="text-gray-900 dark:text-white text-sm font-bold">Rp {{ number_format($total_cost, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Documents Update --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Documents</label>
                    
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
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:button variant="ghost" type="button" wire:click="closeEditModal">Cancel</flux:button>
                <flux:button type="submit" variant="primary">Update Procurement</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Connected Assets Section --}}
    <div class="space-y-4">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">Generated Assets ({{ count($procurement->assets) }})</h2>

        @if(count($procurement->assets) > 0)
            <div class="overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="w-12">#</flux:table.column>
                        <flux:table.column>Asset Code</flux:table.column>
                        <flux:table.column>Asset Name</flux:table.column>
                        <flux:table.column>Category</flux:table.column>
                        <flux:table.column>Location</flux:table.column>
                        <flux:table.column>Status</flux:table.column>
                        <flux:table.column>Purchase Price</flux:table.column>
                        <flux:table.column>Actions</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($this->assets as $asset)
                            <flux:table.row :key="$asset->id">
                                <flux:table.cell>
                                    <flux:text variant="subtle">{{ ($this->assets->currentPage() - 1) * $assetsPerPage + $loop->iteration }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text variant="strong" class="font-mono">{{ $asset->asset_code }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text>{{ $asset->name }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge color="blue" inset="top bottom">
                                        {{ $asset->category->name }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text>{{ $asset->location->name }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    @if($asset->status === 'aktif')
                                        <flux:badge color="green" inset="top bottom">Active</flux:badge>
                                    @elseif($asset->status === 'dipinjam')
                                        <flux:badge color="yellow" inset="top bottom">On Loan</flux:badge>
                                    @else
                                        <flux:badge color="red" inset="top bottom">Inactive</flux:badge>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:text>Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <a href="" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-medium">
                                        View
                                    </a>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            {{-- Pagination --}}
            @if($this->assets->hasPages())
                <div class="mt-6">
                    <flux:pagination :paginator="$this->assets" />
                </div>
            @endif
        @else
            <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-gray-700 p-8 text-center">
                <svg class="h-12 w-12 text-gray-400 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <p class="text-gray-600 dark:text-gray-400">No assets generated from this procurement yet.</p>
            </div>
        @endif
    </div>
</div>
