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
    public $sortField = 'name';
    public $sortOrder = 'asc';
    public $perPage = 10;
    public $selectedEmployees = [];
    public $selectAll = false;

    public function mount()
    {
        $this->resetForm();
    }

    /**
     * Get filtered and paginated employees
     */
    public function getEmployees()
    {
        return Employee::query()
            ->select('employees.*')
            ->with(['user:id,name,email'])
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('nik', 'like', "%{$this->search}%")
                      ->orWhere('position', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortOrder)
            ->paginate($this->perPage);
    }

    /**
     * Toggle sort direction
     */
    public function toggleSort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortOrder = 'asc';
        }
    }

    /**
     * Updated hook - reset page on search, not on sort
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[\Livewire\Attributes\Computed]
    public function availableUsers()
    {
        return User::select('id', 'name', 'email')
            ->whereDoesntHave('employee')
            ->orderBy('name')
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function employees()
    {
        return Employee::with('user:id,name,email')
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('nik', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortField, $this->sortOrder)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.employee-manager', [
            'employees' => $this->employees,
            'users' => $this->availableUsers,
            'genderOptions' => Employee::getGenderOptions(),
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
            'perPage' => $this->perPage,
        ]);
    }

    public function toggleSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedEmployees = $this->employees()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedEmployees = [];
        }
    }

    public function toggleSelect($employeeId)
    {
        $employeeId = (string)$employeeId;
        if (in_array($employeeId, $this->selectedEmployees)) {
            $this->selectedEmployees = array_filter($this->selectedEmployees, fn($id) => $id !== $employeeId);
        } else {
            $this->selectedEmployees[] = $employeeId;
        }
        $this->selectAll = false;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedEmployees)) {
            return;
        }

        Employee::whereIn('id', $this->selectedEmployees)->delete();
        $this->selectedEmployees = [];
        $this->selectAll = false;
        session()->flash('message', 'Selected employees deleted successfully.');
    }

    public function bulkUnlinkUsers()
    {
        if (empty($this->selectedEmployees)) {
            return;
        }

        Employee::whereIn('id', $this->selectedEmployees)->update(['user_id' => null]);
        $this->selectedEmployees = [];
        $this->selectAll = false;
        session()->flash('message', 'Selected employees unlinked from users successfully.');
    }

    public function clearSelection()
    {
        $this->selectedEmployees = [];
        $this->selectAll = false;
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
