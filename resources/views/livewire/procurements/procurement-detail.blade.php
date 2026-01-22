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

    @if (session()->has('error'))
        <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/50">
            <div class="flex">
                <div class="flex-shrink-0">
                    <flux:icon.x-circle class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl" class="text-gray-900 dark:text-white">{{ $procurement->name }}</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">
                @if($procurement->invoice_number)
                    Invoice: {{ $procurement->invoice_number }} •
                @endif
                Created {{ $procurement->created_at->format('d M Y') }}
            </flux:subheading>
        </div>
        <flux:button variant="primary" wire:click="openEditModal" icon="pencil">
            Edit Procurement
        </flux:button>
    </div>

    <flux:separator />

    <flux:separator />

    {{-- Procurement Information Card --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column - Basic Information --}}
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Procurement Information</flux:heading>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Product Name</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white font-medium">{{ $procurement->name }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Category</flux:text>
                    <div class="mt-1">
                        <flux:badge color="blue" size="sm">
                            {{ $procurement->category->name }}
                        </flux:badge>
                    </div>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white">{{ $procurement->supplier->name }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Location</flux:text>
                    <div class="mt-1 flex items-center gap-1">
                        <flux:icon.map-pin class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                        <flux:text class="text-gray-900 dark:text-white">{{ $procurement->location->name }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Procurement Date</flux:text>
                    <div class="mt-1 flex items-center gap-1">
                        <flux:icon.calendar class="h-4 w-4 text-gray-400" />
                        <flux:text class="text-gray-900 dark:text-white">{{ $procurement->procurement_date->format('d F Y') }}</flux:text>
                    </div>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Number</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white font-mono">{{ $procurement->invoice_number ?? '-' }}</flux:text>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white">{{ $procurement->creator->name ?? 'System' }}</flux:text>
                </div>
            </div>
        </div>

        {{-- Middle Column - Pricing Information --}}
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Pricing Information</flux:heading>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity</flux:text>
                    <div class="mt-1">
                        <flux:badge color="zinc" size="lg" variant="soft">{{ $procurement->quantity }} units</flux:badge>
                    </div>
                </div>

                <div>
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit Price</flux:text>
                    <flux:text class="mt-1 text-gray-900 dark:text-white font-medium text-lg">
                        Rp {{ number_format($procurement->unit_price, 0, ',', '.') }}
                    </flux:text>
                </div>

                <flux:separator />

                <div class="pt-2">
                    <flux:text class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Cost</flux:text>
                    <flux:text class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">
                        Rp {{ number_format($procurement->total_cost, 0, ',', '.') }}
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- Right Column - Documents --}}
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <flux:heading size="lg">Documents</flux:heading>
            </div>
            <div class="p-6">
                @if(count($documents) > 0)
                    <div class="space-y-3">
                        @foreach($documents as $doc)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600">
                                <div class="flex items-center gap-3">
                                    <div class="flex-shrink-0">
                                        <flux:icon.document-text class="h-6 w-6 text-red-500" />
                                    </div>
                                    <flux:text size="sm" class="text-gray-700 dark:text-gray-300 truncate max-w-[150px]">{{ basename($doc) }}</flux:text>
                                </div>
                                <a href="{{ Storage::disk('public')->url($doc) }}" 
                                   target="_blank" 
                                   class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
                                >
                                    <flux:icon.arrow-top-right-on-square class="h-5 w-5" />
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <flux:icon.document class="h-10 w-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                        <flux:text class="text-gray-500 dark:text-gray-400 text-sm">No documents attached</flux:text>
                    </div>
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
    <flux:separator />

    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="lg">Generated Assets</flux:heading>
                <flux:subheading class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ count($procurement->assets) }} asset(s) generated from this procurement
                </flux:subheading>
            </div>
        </div>

        @if(count($procurement->assets) > 0)
            <div class="overflow-x-auto">
                <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-12">#</flux:table.column>
                            <flux:table.column>Asset Code</flux:table.column>
                            <flux:table.column>Name</flux:table.column>
                            <flux:table.column>Status</flux:table.column>
                            <flux:table.column>Condition</flux:table.column>
                            <flux:table.column>Location</flux:table.column>
                            <flux:table.column>Created</flux:table.column>
                            <flux:table.column>Actions</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach($this->assets as $asset)
                                <flux:table.row 
                                    :key="$asset->id"
                                    class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                                    wire:click="$dispatch('navigate', { url: '{{ route('assets.show', $asset) }}' })"
                                >
                                    <flux:table.cell>
                                        <flux:text size="sm" variant="subtle">{{ ($this->assets->currentPage() - 1) * $assetsPerPage + $loop->iteration }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text variant="strong" color="blue" class="font-mono">{{ $asset->asset_code }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text variant="strong">{{ $asset->name }}</flux:text>
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
                                                <flux:badge color="yellow" size="sm">
                                                    <flux:icon.arrow-right-circle class="size-3 mr-1" />
                                                    On Loan
                                                </flux:badge>
                                                @break
                                            @case('dipelihara')
                                                <flux:badge color="blue" size="sm">
                                                    <flux:icon.wrench class="size-3 mr-1" />
                                                    Maintenance
                                                </flux:badge>
                                                @break
                                            @case('nonaktif')
                                                <flux:badge color="red" size="sm">
                                                    <flux:icon.x-circle class="size-3 mr-1" />
                                                    Inactive
                                                </flux:badge>
                                                @break
                                            @default
                                                <flux:badge color="zinc" size="sm">{{ $asset->status }}</flux:badge>
                                        @endswitch
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if($asset->condition === 'baik')
                                            <flux:badge color="green" size="sm" variant="soft">Good</flux:badge>
                                        @elseif($asset->condition === 'rusak_ringan')
                                            <flux:badge color="yellow" size="sm" variant="soft">Minor Damage</flux:badge>
                                        @elseif($asset->condition === 'rusak_berat')
                                            <flux:badge color="red" size="sm" variant="soft">Major Damage</flux:badge>
                                        @else
                                            <flux:badge color="zinc" size="sm" variant="soft">{{ $asset->condition ?? '-' }}</flux:badge>
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <div class="flex items-center gap-1">
                                            <flux:icon.map-pin class="h-4 w-4 text-blue-500 dark:text-blue-400" />
                                            <flux:text>{{ $asset->location->name ?? '-' }}</flux:text>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text variant="subtle" size="sm">{{ $asset->created_at->diffForHumans() }}</flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell onclick="event.stopPropagation()">
                                        <flux:button 
                                            variant="ghost" 
                                            size="sm" 
                                            icon="eye"
                                            href="{{ route('assets.show', $asset) }}"
                                            wire:navigate
                                        />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($this->assets->hasPages())
                <div class="mt-6">
                    <flux:pagination :paginator="$this->assets" />
                </div>
            @endif
        @else
            <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden bg-white dark:bg-zinc-800 p-12 text-center">
                <flux:icon.cube class="h-12 w-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                <flux:heading size="lg" class="text-zinc-600 dark:text-zinc-400">No assets generated</flux:heading>
                <flux:text class="mt-2 text-zinc-500">Assets will appear here once they are generated from this procurement.</flux:text>
            </div>
        @endif
    </div>
</div>
