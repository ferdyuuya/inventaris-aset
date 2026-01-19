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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Account Management</h1>
            <flux:modal.trigger name="createUser" wire:click="showCreateForm">
                <flux:button variant="primary">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add User
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search users by name, email, or role..."
                       clearable />
        </div>
    </div>

    {{-- Users Table --}}
    <div class="overflow-x-auto">
        <flux:table>
            <flux:table.columns>
                <flux:table.column class="w-12">#</flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">
                    Name
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'email'" :direction="$sortOrder" wire:click="toggleSort('email')">
                    Email
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'role'" :direction="$sortOrder" wire:click="toggleSort('role')">
                    Role
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'employee_id'" :direction="$sortOrder" wire:click="toggleSort('employee_id')">
                    Employee
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortField === 'created_at'" :direction="$sortOrder" wire:click="toggleSort('created_at')">
                    Created
                </flux:table.column>
                <flux:table.column>
                    Actions
                </flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse($users as $user)
                    <flux:table.row :key="$user->id">
                        <flux:table.cell>
                            <flux:text variant="subtle">{{ ($users->currentPage() - 1) * $perPage + $loop->iteration }}</flux:text>
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
                            <flux:text>{{ $user->email }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge :color="$user->role === 'admin' ? 'purple' : 'blue'" variant="solid">
                                {{ ucfirst($user->role) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            @if($user->employee)
                                <flux:badge color="success" inset="top bottom">
                                    {{ $user->employee->name }}
                                </flux:badge>
                            @else
                                <flux:text variant="subtle">No employee</flux:text>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:text>{{ $user->created_at->format('M d, Y') }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown position="bottom" align="end">
                                <flux:button variant="ghost" icon="ellipsis-horizontal" />
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
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center py-8">
                            <div class="flex flex-col items-center justify-center">
                                <flux:icon.inbox class="h-12 w-12 text-gray-400 dark:text-gray-600 mb-3" />
                                <flux:text variant="subtle">No users found</flux:text>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
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
