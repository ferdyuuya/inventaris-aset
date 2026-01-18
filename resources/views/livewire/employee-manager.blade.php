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
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Employee Management</h1>
            <flux:modal.trigger name="createEmployee">
                <flux:button variant="primary" wire:click="showCreateForm">
                    <svg class="-ml-1 mr-th2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add Employee
                </flux:button>
            </flux:modal.trigger>
        </div>
    </div>

    {{-- Search --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1">
            <flux:input wire:model.live.debounce.300ms="search" 
                       icon="magnifying-glass"
                       placeholder="Search employees by name, NIK, or position..."
                       clearable />
        </div>
    </div>

    {{-- Create Employee Modal --}}
    <flux:modal name="createEmployee" class="md:w-96">
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

            {{-- <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Account Link</flux:heading>
                </div>
                <div>
                    <flux:select wire:model="user_id" label="User Account" description="Link to existing user account (optional)">
                        <flux:select.option value="">-- No User Account --</flux:select.option>
                        @foreach($users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div> --}}

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
    <flux:modal name="editEmployee" class="md:w-96">
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

            {{-- <div class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                <div>
                    <flux:heading>Account Link</flux:heading>
                </div>
                <div>
                    <flux:select wire:model="user_id" label="User Account" description="Link to existing user account (optional)">
                        <flux:select.option value="">-- No User Account --</flux:select.option>
                        @foreach($users as $user)
                            <flux:select.option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</flux:select.option>
                        @endforeach
                    </flux:select>
                    @error('user_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div> --}}

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
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="w-12">
                <input type="checkbox" 
                       wire:model.live="selectAll" 
                       wire:click="toggleSelectAll"
                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700" />
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'nik'" :direction="$sortOrder" wire:click="toggleSort('nik')">
                NIK
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'name'" :direction="$sortOrder" wire:click="toggleSort('name')">
                Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'gender'" :direction="$sortOrder" wire:click="toggleSort('gender')">
                Gender
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'position'" :direction="$sortOrder" wire:click="toggleSort('position')">
                Position
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'phone'" :direction="$sortOrder" wire:click="toggleSort('phone')">
                Phone
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortField === 'user_id'" :direction="$sortOrder" wire:click="toggleSort('user_id')">
                User Account
            </flux:table.column>
            <flux:table.column>
                Actions
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($employees as $employee)
                <flux:table.row :key="$employee->id" class="{{ in_array((string)$employee->id, $selectedEmployees) ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                    <flux:table.cell class="w-12">
                        <input type="checkbox" 
                               value="{{ $employee->id }}"
                               wire:model.live="selectedEmployees"
                               wire:click="toggleSelect({{ $employee->id }})"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700" />
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">
                        {{ $employee->nik }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $employee->name }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $employee->gender }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $employee->position }}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{ $employee->phone ?? '-' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if($employee->user)
                            <flux:badge size="sm" color="green" inset="top bottom">
                                {{ $employee->user->name }}
                            </flux:badge>
                        @else
                            <span class="text-gray-400 dark:text-gray-500 text-sm">No account</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="left" align="end">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset />
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
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-sm text-gray-500 dark:text-gray-400 py-8">
                        No employees found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

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
