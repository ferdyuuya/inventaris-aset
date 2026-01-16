<?php

namespace App\Livewire;

use App\Models\AssetCategory;
use App\Models\Procurement;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;

class ProcurementManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|exists:asset_categories,id')]
    public $asset_category_id = '';
    
    #[Validate('required|exists:suppliers,id')]
    public $supplier_id = '';
    
    #[Validate('required|date')]
    public $procurement_date = '';
    
    #[Validate('nullable|string|max:255')]
    public $invoice_number = '';
    
    #[Validate('required|numeric|min:0')]
    public $total_cost = '';

    // Component state
    public $selectedProcurementId = null;
    public $isEditing = false;
    public $showForm = false;
    public $search = '';

    public function mount()
    {
        $this->resetForm();
        // Set current date as default
        if (!$this->procurement_date) {
            $this->procurement_date = now()->format('Y-m-d');
        }
    }

    public function render()
    {
        $procurements = Procurement::with(['supplier', 'category', 'creator'])
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('procurement_date', 'desc')
            ->paginate(10);

        $suppliers = Supplier::orderBy('name')->get();
        $categories = AssetCategory::orderBy('name')->get();

        return view('livewire.procurement-manager', [
            'procurements' => $procurements,
            'suppliers' => $suppliers,
            'categories' => $categories,
        ]);
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->procurement_date = now()->format('Y-m-d');
        $this->showForm = true;
        $this->isEditing = false;
        $this->modal('createProcurement')->show();
    }

    public function edit($procurementId)
    {
        $procurement = Procurement::findOrFail($procurementId);
        
        $this->selectedProcurementId = $procurement->id;
        $this->name = $procurement->name;
        $this->asset_category_id = $procurement->asset_category_id;
        $this->supplier_id = $procurement->supplier_id;
        $this->procurement_date = $procurement->procurement_date->format('Y-m-d');
        $this->invoice_number = $procurement->invoice_number;
        $this->total_cost = $procurement->total_cost;
        
        $this->isEditing = true;
        $this->showForm = true;
        $this->modal('editProcurement')->show();
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateProcurement();
        } else {
            $this->createProcurement();
        }
    }

    public function createProcurement()
    {
        $this->validate();

        Procurement::create([
            'name' => $this->name,
            'asset_category_id' => $this->asset_category_id,
            'supplier_id' => $this->supplier_id,
            'procurement_date' => $this->procurement_date,
            'invoice_number' => $this->invoice_number ?: null,
            'total_cost' => $this->total_cost,
            'created_by' => Auth::id(),
        ]);

        $this->resetForm();
        $this->showForm = false;
        $this->modal('createProcurement')->close();
        $this->dispatch('procurement-created');
        session()->flash('message', 'Procurement created successfully.');
    }

    public function updateProcurement()
    {
        $this->validate();

        $procurement = Procurement::findOrFail($this->selectedProcurementId);
        $procurement->update([
            'name' => $this->name,
            'asset_category_id' => $this->asset_category_id,
            'supplier_id' => $this->supplier_id,
            'procurement_date' => $this->procurement_date,
            'invoice_number' => $this->invoice_number ?: null,
            'total_cost' => $this->total_cost,
        ]);

        $this->resetForm();
        $this->showForm = false;
        $this->modal('editProcurement')->close();
        $this->dispatch('procurement-updated');
        session()->flash('message', 'Procurement updated successfully.');
    }

    public function delete($procurementId)
    {
        Procurement::findOrFail($procurementId)->delete();
        $this->dispatch('procurement-deleted');
        session()->flash('message', 'Procurement deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->showForm = false;
        $this->modal('createProcurement')->close();
        $this->modal('editProcurement')->close();
    }

    public function resetForm()
    {
        $this->name = '';
        $this->asset_category_id = '';
        $this->supplier_id = '';
        $this->procurement_date = now()->format('Y-m-d');
        $this->invoice_number = '';
        $this->total_cost = '';
        $this->selectedProcurementId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'procurement_date' => 'required|date',
            'invoice_number' => 'nullable|string|max:255',
            'total_cost' => 'required|numeric|min:0',
        ];
    }
}
