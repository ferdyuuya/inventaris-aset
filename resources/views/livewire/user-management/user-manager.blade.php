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
            <flux:heading size="xl" class="text-gray-900 dark:text-white">User Account Management</flux:heading>
            <flux:subheading class="text-gray-600 dark:text-gray-400 mt-2">Manage user accounts and permissions</flux:subheading>
        </div>
        <flux:modal.trigger name="createUser" wire:click="showCreateForm">
            <flux:button variant="primary" icon="plus">
                Add User
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
                           placeholder="Search users by name, email, or role..."
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

    {{-- Users Table --}}
    <div class="overflow-x-auto">
        <div class="shadow-sm ring-1 ring-gray-200 dark:ring-gray-700 rounded-lg overflow-hidden">
        @if($users->count() > 0)
            <flux:table>
                <flux:table.columns>
                    <flux:table.column class="w-12">#</flux:table.column>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Email</flux:table.column>
                    <flux:table.column>Role</flux:table.column>
                    <flux:table.column>Employee</flux:table.column>
                    <flux:table.column>Created</flux:table.column>
                    <flux:table.column>Actions</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($users as $user)
                        <flux:table.row 
                            :key="$user->id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            wire:click="showEditForm({{ $user->id }})"
                        >
                            <flux:table.cell>
                                <flux:text size="sm" variant="subtle">{{ ($users->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-2">
                                    <div class="h-8 w-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ $user->initials() }}
                                    </div>
                                    <flux:text variant="strong">{{ $user->name }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:text size="sm">{{ $user->email }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($user->role === 'admin')
                                    <flux:badge color="purple" size="sm">
                                        <flux:icon.shield-check class="size-3 mr-1" />
                                        Admin
                                    </flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm" variant="soft">
                                        <flux:icon.user class="size-3 mr-1" />
                                        Staff
                                    </flux:badge>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                @if($user->employee)
                                    <flux:badge color="green" size="sm" variant="soft">
                                        <flux:icon.link class="size-3 mr-1" />
                                        {{ $user->employee->name }}
                                    </flux:badge>
                                @else
                                    <flux:text size="sm" variant="subtle">No employee</flux:text>
                                @endif
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="flex items-center gap-1">
                                    <flux:icon.calendar class="size-3 text-gray-400" />
                                    <flux:text size="sm">{{ $user->created_at->format('d M Y, H:i') }}</flux:text>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell onclick="event.stopPropagation()">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button variant="ghost" size="sm" icon="eye" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil" wire:click="showEditForm({{ $user->id }})">Edit</flux:menu.item>
                                        @if($user->employee)
                                            <flux:menu.item icon="link-slash" variant="danger" wire:click="unlinkEmployee({{ $user->id }})">Unlink Employee</flux:menu.item>
                                        @else
                                            <flux:menu.item icon="link" wire:click="showLinkEmployeeModal({{ $user->id }})">Link Employee</flux:menu.item>
                                        @endif
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="showDeleteConfirmation({{ $user->id }})">Delete</flux:menu.item>
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
                <flux:heading size="lg" class="mt-4 text-zinc-600 dark:text-zinc-400">No users found</flux:heading>
                <flux:text class="mt-2 text-zinc-500">
                    @if($search)
                        Try adjusting your search term.
                    @else
                        Get started by creating your first user.
                    @endif
                </flux:text>
            </div>
        @endif
        </div>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
        <div class="mt-6">
            <flux:pagination :paginator="$users" />
        </div>
    @endif

    {{-- Create User Modal --}}
    <flux:modal name="createUser" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="createUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Create New User</flux:heading>
                <flux:text class="mt-2 text-sm">Enter the user's basic information to create a new account.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Personal Information</flux:heading>
                </div>
                <div>
                    <flux:input wire:model="name" icon="user" label="Full Name" description="The user's complete name" placeholder="Enter full name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="email" icon="envelope" label="Email Address" description="Will be used for login" type="email" placeholder="user@example.com" required />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="password" icon="lock-closed" label="Password" description="Minimum 8 characters" type="password" placeholder="Enter password" required viewable />
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Account Settings</flux:heading>
                </div>
                <div>
                    <flux:select wire:model="role" label="User Role" description="Determines user permissions" placeholder="Select a role" required>
                        <flux:select.option value="">-- Select Role --</flux:select.option>
                        @foreach($roleOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Create User</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Edit User Modal --}}
    <flux:modal name="editUser" class="md:w-96" @close="$wire.resetForm()">
        <form wire:submit="updateUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Edit User</flux:heading>
                <flux:text class="mt-2 text-sm">Update the user's information and account settings.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Personal Information</flux:heading>
                </div>
                <div>
                    <flux:input wire:model="name" icon="user" label="Full Name" description="The user's complete name" placeholder="Enter user name" required />
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="email" icon="envelope" label="Email Address" description="Will be used for login" type="email" placeholder="user@example.com" required />
                    @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <flux:input wire:model="password" icon="lock-closed" label="Password" description="Leave blank to keep current password" type="password" placeholder="Leave blank to keep current password" viewable />
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Account Settings</flux:heading>
                </div>
                <div>
                    <flux:select wire:model="role" label="User Role" description="Determines user permissions" placeholder="Select a role" required>
                        <flux:select.option value="">-- Select Role --</flux:select.option>
                        @foreach($roleOptions as $value => $label)
                            <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Update User</flux:button>
            </div>
        </form>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal name="deleteUser" class="md:w-96">
        @if($userToDelete)
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Delete User Account</flux:heading>
                <flux:text class="mt-2">
                    Are you sure you want to delete <strong>{{ $userToDelete->name }}</strong>? This action cannot be undone.
                </flux:text>
                @if($userToDelete->employee)
                    <flux:callout type="warning" class="mt-4">
                        This user is linked to an employee record. The link will be removed, but the employee record will remain.
                    </flux:callout>
                @endif
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button wire:click="confirmDelete" variant="danger">Delete User</flux:button>
            </div>
        </div>
        @endif
    </flux:modal>

    {{-- Link Employee Modal --}}
    <flux:modal name="linkEmployee" class="md:w-96">
        <form wire:submit="linkEmployee" class="space-y-6">
            <div>
                <flux:heading size="lg">Link User to Employee</flux:heading>
                <flux:text class="mt-2 text-sm">Select an employee to link with this user account.</flux:text>
            </div>

            <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:input wire:model.live.debounce.300ms="employeeSearchQuery" 
                               icon="magnifying-glass"
                               label="Search Employee"
                               placeholder="Search by name or email..."
                               clearable />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Available Employees</label>
                    <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                        @forelse($this->getAvailableEmployees() as $employee)
                            <label class="flex items-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer">
                                <input type="radio" wire:model="selectedEmployeeId" value="{{ $employee->id }}" class="form-radio">
                                <span class="ml-3 text-sm text-gray-900 dark:text-white">
                                    {{ $employee->name }}
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $employee->email }}</span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                                No available employees to link
                            </p>
                        @endforelse
                    </div>
                    @error('selectedEmployeeId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex gap-2 border-t border-gray-200 dark:border-gray-700 pt-4">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">Link Employee</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
