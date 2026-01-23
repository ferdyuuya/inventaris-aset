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
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Asset Categories</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage asset categories and classifications</flux:subheading>
        </div>
        <flux:modal.trigger name="createCategory" wire:click="showCreateForm">
            <flux:button variant="primary" icon="plus">
                Add Category
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
                       placeholder="Search categories by name or description..."
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

    {{-- Categories Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($categories->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Code</flux:table.column>
                    <flux:table.column>Description</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($categories as $category)
                        <flux:table.row 
                            :key="$category->id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="showEditForm({{ $category->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($categories->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <flux:icon.tag class="size-4 text-gray-400" />
                                    <flux:text variant="strong">{{ $category->name }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="blue" size="sm">
                                    {{ $category->code }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm" class="text-zinc-500">{{ Str::limit($category->description, 40) ?? '-' }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $category->created_at->format('d M Y, H:i') }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="showEditForm({{ $category->id }})">Edit</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $category->id }})">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="p-12 text-center">
                <flux:icon.tag class="size-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <flux:heading size="lg" class="text-gray-900 dark:text-white">No categories found</flux:heading>
                <flux:text class="text-gray-500 dark:text-gray-400 mt-1">Get started by creating a new category.</flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$categories" />
        </div>
    @endif

    {{-- Create Category Modal --}}
    <flux:modal name="createCategory" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Create New Category</flux:heading>
                <flux:text class="mt-2 text-sm">Enter the asset category information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="tag" label="Category Name" description="The name of the asset category" placeholder="Enter category name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="code" icon="identification" label="Category Code" description="The code of the asset category" placeholder="Enter category code" required />
                    @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <flux:input wire:model="description" icon="document-text" label="Description" description="Optional description for this category" placeholder="Enter description" />
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create Category</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Category Modal --}}
    <flux:modal name="editCategory" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Category</flux:heading>
                <flux:text class="mt-2 text-sm">Update the asset category information.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model="name" icon="tag" label="Category Name" description="The name of the asset category" placeholder="Enter category name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="code" icon="identification" label="Category Code" description="The code of the asset category" placeholder="Enter category code" required />
                    @error('code') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="description" icon="document-text" label="Description" description="Optional description for this category" placeholder="Enter description" />
                    @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Category</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="deleteCategory" class="md:w-96">
        @if($categoryToDelete)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete Category</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete <strong>{{ $categoryToDelete->name }}</strong>? This action cannot be undone.
                </flux:text>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDelete" variant="danger">Delete Category</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>
</div>
