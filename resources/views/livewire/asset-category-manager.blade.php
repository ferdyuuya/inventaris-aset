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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Asset Category Management</h1>
            <flux:modal.trigger name="createCategory" wire:click="showCreateForm">
                <flux:button variant="primary">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Category
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search categories by name or description..."
                       clearable />
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">
                    Name
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'code'" :direction="$sortOrder" wire:click="toggleSort('code')">
                    Code
                </flux:table.column>
                <flux:table.column>
                    Description
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortOrder" wire:click="toggleSort('created_at')">
                    Created
                </flux:table.column>
                <flux:table.column>
                    Actions
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell>
                            <flux:text size="sm" variant="subtle">{{ ($categories->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text variant="strong">{{ $category->name }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="blue" variant="outline">
                                {{ $category->code }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm">{{ $category->description ?? '-' }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text size="sm">{{ $category->created_at->format('M d, Y') }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="showEditForm({{ $category->id }})">Edit</flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $category->id }})">Delete</flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="text-center py-8">
                            <div class="flex flex-col items-center justify-center">
                                <flux:icon.inbox class="h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                <flux:text variant="subtle">No categories found</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- Pagination --}}
    @if($categories->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$categories" />
        </div>
    @endif

    {{-- Create Category Modal --}}
    <flux:modal name="createCategory" class="md:w-96">
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
    <flux:modal name="editCategory" class="md:w-96">
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
