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
            <flux:heading size="xl" class="text-gray-900 dark:text-white">Employee Management</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage employee records and user account linkages</flux:subheading>
        </div>
        <flux:modal.trigger name="createEmployee">
            <flux:button variant="primary" icon="plus" wire:click="showCreateForm">
                Add Employee
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:separator />

    {{-- Search and Filter Bar --}}
    <div class="space-y-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
            {{-- Search Input --}}
            <div class="flex-1 max-w-md">
                <flux:input wire:model.live.debounce.300ms="search" 
                           icon="magnifying-glass"
                           placeholder="Search employees by name, NIK, or position..."
                           clearable />
            </div>

            {{-- Vertical Separator --}}
            <div class="hidden lg:block w-px h-8 bg-gray-300 dark:bg-gray-600"></div>

            {{-- Sorting Dropdown --}}
            <div class="flex flex-wrap gap-3">
                <flux:dropdown position="bottom" align="start">
                    <flux:button variant="ghost" size="sm" icon="arrows-up-down">
                        Sort
                    </flux:button>

                    <flux:menu>
                        <flux:text class="px-3 py-2 text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sort By</flux:text>
                        <flux:separator />
                        
                        {{-- Newest --}}
                        <flux:menu.item
                            wire:click="$set('sortField', 'created_at'); $set('sortOrder', 'desc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'desc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'desc',
                            ])>
                                Newest
                            </span>
                        </flux:menu.item>

                        {{-- Oldest --}}
                        <flux:menu.item
                            wire:click="$set('sortField', 'created_at'); $set('sortOrder', 'asc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'created_at' && $sortOrder === 'asc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'created_at' && $sortOrder === 'asc',
                            ])>
                                Oldest
                            </span>
                        </flux:menu.item>

                        <flux:separator />

                        {{-- A-Z --}}
                        <flux:menu.item
                            wire:click="$set('sortField', 'name'); $set('sortOrder', 'asc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'asc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'asc',
                            ])>
                                A–Z
                            </span>
                        </flux:menu.item>

                        {{-- Z-A --}}
                        <flux:menu.item
                            wire:click="$set('sortField', 'name'); $set('sortOrder', 'desc')"
                            @class([
                                'bg-blue-50 dark:bg-blue-900/30' => $sortField === 'name' && $sortOrder === 'desc',
                            ])
                        >
                            <span @class([
                                'font-semibold text-blue-600 dark:text-blue-400' => $sortField === 'name' && $sortOrder === 'desc',
                            ])>
                                Z–A
                            </span>
                        </flux:menu.item>
                    </flux:menu>
                </flux:dropdown>
            </div>
        </div>
    </div>

    <flux:separator />

    {{-- Create Employee Modal --}}
    <flux:modal name="createEmployee" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Add New Employee</flux:heading>
                <flux:text class="mt-2 text-sm">Enter the employee's information and link to a user account if needed.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Employee Details</flux:heading>
                </div>
                <div>
                    <flux:input wire:model="nik" icon="identification" label="NIK" description="National identification number" placeholder="Enter NIK" required />
                    @error('nik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="name" icon="user" label="Full Name" description="Employee's complete name" placeholder="Enter employee name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="gender" label="Gender" description="Employee gender" placeholder="Select gender" required>
                        <flux:select.option value="">-- Select Gender --</flux:select.option>
                        @foreach($genderOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="phone" icon="phone" label="Phone Number" description="Employee contact number" placeholder="Enter phone number" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="position" icon="briefcase" label="Position" description="Job title or position" placeholder="Enter position" required />
                    @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create Employee</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit Employee Modal --}}
    <flux:modal name="editEmployee" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit Employee</flux:heading>
                <flux:text class="mt-2 text-sm">Update the employee's information and account linkage.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Employee Details</flux:heading>
                </div>
                <div>
                    <flux:input wire:model="nik" icon="identification" label="NIK" description="National identification number" placeholder="Enter NIK" required />
                    @error('nik') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="name" icon="user" label="Full Name" description="Employee's complete name" placeholder="Enter employee name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:select wire:model="gender" label="Gender" description="Employee gender" placeholder="Select gender" required>
                        <flux:select.option value="">-- Select Gender --</flux:select.option>
                        @foreach($genderOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('gender') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="phone" icon="phone" label="Phone Number" description="Employee contact number" placeholder="Enter phone number" />
                    @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="position" icon="briefcase" label="Position" description="Job title or position" placeholder="Enter position" required />
                    @error('position') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update Employee</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Bulk Actions Bar --}}
    @if(count($selectedEmployees) > 0)
        <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-blue-900 dark:text-blue-200">
                    {{ count($selectedEmployees) }} employee{{ count($selectedEmployees) > 1 ? 's' : '' }} selected
                </span>
            </div>
            <div class="flex items-center space-x-2">
                <flux:button size="sm" variant="ghost" wire:click="clearSelection">
                    Clear Selection
                </flux:button>
                <flux:button size="sm" variant="danger" icon="trash" wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selectedEmployees) }} employee(s)?">
                    Delete Selected
                </flux:button>
                <flux:button size="sm" variant="ghost" icon="link-slash" wire:click="bulkUnlinkUsers">
                    Unlink Users
                </flux:button>
            </div>
        </div>
    @endif

    {{-- Employee Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($employees->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>NIK</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Gender</flux:table.column>
                    <flux:table.column>Position</flux:table.column>
                    <flux:table.column>Phone</flux:table.column>
                    <flux:table.column>User Account</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($employees as $employee)
                        <flux:table.row 
                            :key="$employee->id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="edit({{ $employee->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($employees->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong" color="blue">{{ $employee->nik }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text variant="strong">{{ $employee->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($employee->gender === 'Laki-Laki')
                                    <flux:badge color="sky" size="sm" variant="soft">
                                        {{ $employee->gender }}
                                    </flux:badge>
                                @else
                                    <flux:badge color="pink" size="sm" variant="soft">
                                        {{ $employee->gender }}
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:badge color="zinc" size="sm" variant="soft">
                                    <flux:icon.briefcase class="size-3 mr-1" />
                                    {{ $employee->position }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm">{{ $employee->phone ?? '-' }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($employee->user)
                                    <flux:badge color="green" size="sm" variant="soft">
                                        <flux:icon.link class="size-3 mr-1" />
                                        {{ $employee->user->name }}
                                    </flux:badge>
                                @else
                                    <flux:text size="sm" variant="subtle">No account</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="edit({{ $employee->id }})">Edit</flux:menu.item>
                                        @if($employee->user)
                                            <flux:menu.item icon="link-slash" variant="danger" wire:click="unlinkUser({{ $employee->id }})">Unlink User</flux:menu.item>
                                        @else
                                            <flux:menu.item icon="link" wire:click="showLinkUserModal({{ $employee->id }})">Link User</flux:menu.item>
                                        @endif
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $employee->id }})" wire:confirm="Are you sure you want to delete this employee?">Delete</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        @else
            <div class="text-center py-12">
                <flux:icon.inbox class="mx-auto size-12 text-zinc-300 dark:text-zinc-600" />
                <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No employees found</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    @if($search)
                        Try adjusting your search term.
                    @else
                        Get started by adding your first employee.
                    @endif
                </flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($employees->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$employees" />
        </div>
    @endif

    {{-- Link User Modal --}}
    <flux:modal name="linkUser" class="md:w-96">
        <form wire:submit="linkUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Link Employee to User</flux:heading>
                <flux:text class="mt-2 text-sm">Select a user account to link with this employee.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model.live.debounce.300ms="userSearchQuery" 
                               icon="magnifying-glass"
                               label="Search User"
                               placeholder="Search by name or email..."
                               clearable />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available Users</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        @forelse($this->getAvailableUsers() as $user)
                            <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer">
                                <input type="radio" wire:model="selectedUserId" value="{{ $user->id }}" class="form-radio">
                                <span class="ml-3 text-sm text-gray-900 dark:text-white">
                                    {{ $user->name }}
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $user->email }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No available users to link
                            </p>
                        @endforelse
                    </div>
                    @error('selectedUserId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Link User</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
