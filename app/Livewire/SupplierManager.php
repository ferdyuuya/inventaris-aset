<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class SupplierManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('nullable|string')]
    public $address = '';
    
    #[Validate('nullable|string|max:255')]
    public $phone = '';
    
    #[Validate('nullable|email|max:255')]
    public $email = '';

    // Component state
    public $selectedSupplierId = null;
    public $isEditing = false;
    public $supplierToDelete = null;
    public $search = '';
    public $sortField = 'name';
    public $sortOrder = 'asc';
    public $perPage = 10;

    public function mount()
    {
        $this->resetForm();
    }

    /**
     * Get filtered and paginated suppliers
     */
    #[\Livewire\Attributes\Computed]
    public function suppliers()
    {
        return Supplier::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                      ->orWhere('phone', 'like', "%{$this->search}%");
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

    public function render()
    {
        return view('livewire.supplier-manager', [
            'suppliers' => $this->suppliers,
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
            'perPage' => $this->perPage,
        ]);
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->modal('createSupplier')->show();
    }

    public function showEditForm($supplierId)
    {
        $supplier = Supplier::findOrFail($supplierId);
        
        $this->selectedSupplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->address = $supplier->address;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->isEditing = true;
        
        $this->modal('editSupplier')->show();
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateSupplier();
        } else {
            $this->createSupplier();
        }
    }

    public function createSupplier()
    {
        $this->validate();

        Supplier::create([
            'name' => $this->name,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
        ]);

        $this->modal('createSupplier')->close();
        $this->resetForm();
        session()->flash('message', 'Supplier created successfully.');
    }

    public function updateSupplier()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
        ];

        $this->validate($rules);

        $supplier = Supplier::findOrFail($this->selectedSupplierId);
        $supplier->update([
            'name' => $this->name,
            'address' => $this->address ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
        ]);

        $this->modal('editSupplier')->close();
        $this->resetForm();
        session()->flash('message', 'Supplier updated successfully.');
    }

    public function showDeleteConfirmation($supplierId)
    {
        $this->supplierToDelete = Supplier::findOrFail($supplierId);
        $this->modal('deleteSupplier')->show();
    }

    public function confirmDelete()
    {
        if ($this->supplierToDelete) {
            $this->supplierToDelete->delete();
            $this->modal('deleteSupplier')->close();
            $this->supplierToDelete = null;
            session()->flash('message', 'Supplier deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->address = '';
        $this->phone = '';
        $this->email = '';
        $this->selectedSupplierId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
