<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;

class UserManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|email|max:255|unique:users,email')]
    public $email = '';
    
    #[Validate('required|string|min:8')]
    public $password = '';
    
    #[Validate('required|in:admin,staff')]
    public $role = '';

    // Component state
    public $selectedUserId = null;
    public $isEditing = false;
    public $userToDelete = null;
    public $search = '';
    public $selectedEmployeeId = null;
    public $employeeSearchQuery = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $users = User::with('employee')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('role', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.user-manager', [
            'users' => $users,
            'roleOptions' => $this->getRoleOptions()
        ]);
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->modal('createUser')->show();
    }

    public function showEditForm($userId)
    {
        $user = User::findOrFail($userId);
        
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role;
        
        $this->modal('editUser')->show();
    }

    public function showDeleteConfirmation($userId)
    {
        $this->userToDelete = User::findOrFail($userId);
        $this->showDeleteModal = true;
        $this->modal('deleteUser')->show();
    }

    public function createUser()
    {
        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => $this->role,
            'email_verified_at' => now(),
        ]);

        $this->modal('createUser')->close();
        $this->dispatch('user-created');
        $this->resetForm();
        session()->flash('message', 'User account created successfully.');
    }

    public function updateUser()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->selectedUserId,
            'role' => 'required|in:admin,staff',
        ];

        if (!empty($this->password)) {
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        $user = User::findOrFail($this->selectedUserId);
        $updateData = [
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if (!empty($this->password)) {
            $updateData['password'] = Hash::make($this->password);
        }

        $user->update($updateData);

        $this->modal('editUser')->close();
        $this->dispatch('user-updated');
        $this->resetForm();
        session()->flash('message', 'User account updated successfully.');
    }

    public function confirmDelete()
    {
        if ($this->userToDelete) {
            // Check if user has an employee record
            if ($this->userToDelete->employee) {
                $this->userToDelete->employee->update(['user_id' => null]);
            }
            
            $this->userToDelete->delete();
            $this->modal('deleteUser')->close();
            $this->dispatch('user-deleted');
            $this->userToDelete = null;
            session()->flash('message', 'User account deleted successfully.');
        }
    }

    public function showLinkEmployeeModal($userId)
    {
        $this->selectedUserId = $userId;
        $this->selectedEmployeeId = null;
        $this->employeeSearchQuery = '';
        $this->modal('linkEmployee')->show();
    }

    public function linkEmployee()
    {
        if (!$this->selectedEmployeeId) {
            $this->addError('selectedEmployeeId', 'Please select an employee.');
            return;
        }

        $user = User::findOrFail($this->selectedUserId);
        $user->update(['employee_id' => $this->selectedEmployeeId]);

        $this->closeLinkEmployeeModal();
        session()->flash('message', 'User linked to employee successfully.');
    }

    public function unlinkEmployee($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['employee_id' => null]);
        session()->flash('message', 'User unlinked from employee successfully.');
    }

    public function closeLinkEmployeeModal()
    {
        $this->modal('linkEmployee')->close();
        $this->selectedUserId = null;
        $this->selectedEmployeeId = null;
        $this->employeeSearchQuery = '';
    }

    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role = '';
        $this->selectedUserId = null;
        $this->resetErrorBag();
    }

    public function getAvailableEmployees()
    {
        return \App\Models\Employee::where('user_id', null)
            ->when($this->employeeSearchQuery, function($query) {
                $query->where('name', 'like', '%' . $this->employeeSearchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->employeeSearchQuery . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function getRoleOptions(): array
    {
        return [
            'admin' => 'Administrator',
            'staff' => 'Staff'
        ];
    }
}
