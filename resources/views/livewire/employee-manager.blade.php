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
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    {{-- Employee Table --}}
    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
        <div class="min-w-full">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">NIK</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">User Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $employee->nik }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $employee->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $employee->gender }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $employee->position }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $employee->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            @if($employee->user)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ $employee->user->name }}
                                </span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500">No account</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            No employees found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($employees->hasPages())
        <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
            {{ $employees->links() }}
        </div>
        @endif
    </div>

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
