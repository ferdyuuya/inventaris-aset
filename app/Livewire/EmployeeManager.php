<?php

namespace App\Livewire;

use App\Models\Employee;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class EmployeeManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('nullable|exists:users,id')]
    public $user_id = null;
    
    #[Validate('required|string|max:255|unique:employees,nik')]
    public $nik = '';
    
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|in:Laki-Laki,Perempuan')]
    public $gender = '';
    
    #[Validate('nullable|string|max:255')]
    public $phone = '';
    
    #[Validate('required|string|max:255')]
    public $position = '';

    // Component state
    public $selectedEmployeeId = null;
    public $isEditing = false;
    public $showForm = false;
    public $search = '';
    public $selectedUserId = null;
    public $userSearchQuery = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $employees = Employee::with('user')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('nik', 'like', '%' . $this->search . '%')
                      ->orWhere('position', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $users = User::whereDoesntHave('employee')->get();

        return view('livewire.employee-manager', [
            'employees' => $employees,
            'users' => $users,
            'genderOptions' => Employee::getGenderOptions()
        ]);
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->showForm = true;
        $this->isEditing = false;
        $this->modal('createEmployee')->show();
    }

    public function edit($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        
        $this->selectedEmployeeId = $employee->id;
        $this->user_id = $employee->user_id;
        $this->nik = $employee->nik;
        $this->name = $employee->name;
        $this->gender = $employee->gender;
        $this->phone = $employee->phone;
        $this->position = $employee->position;
        
        $this->isEditing = true;
        $this->showForm = true;
        $this->modal('editEmployee')->show();
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateEmployee();
        } else {
            $this->createEmployee();
        }
    }

    public function createEmployee()
    {
        $this->validate();

        Employee::create([
            'user_id' => $this->user_id ?: null,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'phone' => $this->phone ?: null,
            'position' => $this->position,
        ]);

        $this->resetForm();
        $this->showForm = false;
        $this->modal('createEmployee')->close();
        $this->dispatch('employee-created');
        session()->flash('message', 'Employee created successfully.');
    }

    public function updateEmployee()
    {
        $rules = $this->rules();
        $rules['nik'] = 'required|string|max:255|unique:employees,nik,' . $this->selectedEmployeeId;
        $this->validate($rules);

        $employee = Employee::findOrFail($this->selectedEmployeeId);
        $employee->update([
            'user_id' => $this->user_id ?: null,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'phone' => $this->phone ?: null,
            'position' => $this->position,
        ]);

        $this->resetForm();
        $this->showForm = false;
        $this->modal('editEmployee')->close();
        $this->dispatch('employee-updated');
        session()->flash('message', 'Employee updated successfully.');
    }

    public function delete($employeeId)
    {
        Employee::findOrFail($employeeId)->delete();
        $this->dispatch('employee-deleted');
        session()->flash('message', 'Employee deleted successfully.');
    }

    public function unlinkUser($employeeId)
    {
        $employee = Employee::findOrFail($employeeId);
        $employee->update(['user_id' => null]);
        session()->flash('message', 'Employee unlinked from user successfully.');
    }

    public function showLinkUserModal($employeeId)
    {
        $this->selectedEmployeeId = $employeeId;
        $this->selectedUserId = null;
        $this->userSearchQuery = '';
        $this->modal('linkUser')->show();
    }

    public function linkUser()
    {
        if (!$this->selectedUserId) {
            $this->addError('selectedUserId', 'Please select a user.');
            return;
        }

        $employee = Employee::findOrFail($this->selectedEmployeeId);
        $employee->update(['user_id' => $this->selectedUserId]);

        $this->closeLinkUserModal();
        session()->flash('message', 'Employee linked to user successfully.');
    }

    public function closeLinkUserModal()
    {
        $this->modal('linkUser')->close();
        $this->selectedEmployeeId = null;
        $this->selectedUserId = null;
        $this->userSearchQuery = '';
    }

    public function getAvailableUsers()
    {
        return User::whereDoesntHave('employee')
            ->when($this->userSearchQuery, function($query) {
                $query->where('name', 'like', '%' . $this->userSearchQuery . '%')
                      ->orWhere('email', 'like', '%' . $this->userSearchQuery . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->showForm = false;
        $this->modal('createEmployee')->close();
        $this->modal('editEmployee')->close();
    }

    public function resetForm()
    {
        $this->user_id = null;
        $this->nik = '';
        $this->name = '';
        $this->gender = '';
        $this->phone = '';
        $this->position = '';
        $this->selectedEmployeeId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    protected function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'nik' => 'required|string|max:255|unique:employees,nik',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-Laki,Perempuan',
            'phone' => 'nullable|string|max:255',
            'position' => 'required|string|max:255',
        ];
    }
}
