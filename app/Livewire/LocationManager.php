<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class LocationManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('nullable|string')]
    public $description = '';

    #[Validate('nullable|exists:employees,id')]
    public $responsible_employee_id = null;

    // Component state
    public $selectedLocationId = null;
    public $isEditing = false;
    public $locationToDelete = null;
    public $search = '';
    public $sortField = 'name';
    public $sortOrder = 'asc';
    public $perPage = 10;

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $locations = $this->getLocations();
        $employees = Employee::orderBy('name')->get();

        return view('livewire.location-manager', [
            'locations' => $locations,
            'employees' => $employees,
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function getLocations()
    {
        return Location::with('responsibleEmployee:id,name')
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortOrder)
            ->paginate($this->perPage);
    }

    public function toggleSort($field)
    {
        if ($this->sortField === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortOrder = 'asc';
        }
    }

    #[\Livewire\Attributes\On('search')]
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->modal('createLocation')->show();
    }

    public function showEditForm($locationId)
    {
        $location = Location::findOrFail($locationId);
        
        $this->selectedLocationId = $location->id;
        $this->name = $location->name;
        $this->description = $location->description;
        $this->responsible_employee_id = $location->responsible_employee_id;
        $this->isEditing = true;
        
        $this->modal('editLocation')->show();
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateLocation();
        } else {
            $this->createLocation();
        }
    }

    public function createLocation()
    {
        $this->validate();

        Location::create([
            'name' => $this->name,
            'description' => $this->description,
            'responsible_employee_id' => $this->responsible_employee_id ?: null,
        ]);

        $this->modal('createLocation')->close();
        $this->resetForm();
        session()->flash('message', 'Location created successfully.');
    }

    public function updateLocation()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'responsible_employee_id' => 'nullable|exists:employees,id',
        ];

        $this->validate($rules);

        $location = Location::findOrFail($this->selectedLocationId);
        $location->update([
            'name' => $this->name,
            'description' => $this->description,
            'responsible_employee_id' => $this->responsible_employee_id ?: null,
        ]);

        $this->modal('editLocation')->close();
        $this->resetForm();
        session()->flash('message', 'Location updated successfully.');
    }

    public function showDeleteConfirmation($locationId)
    {
        $this->locationToDelete = Location::findOrFail($locationId);
        $this->modal('deleteLocation')->show();
    }

    public function confirmDelete()
    {
        if ($this->locationToDelete) {
            $this->locationToDelete->delete();
            $this->modal('deleteLocation')->close();
            $this->locationToDelete = null;
            session()->flash('message', 'Location deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->responsible_employee_id = null;
        $this->selectedLocationId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
