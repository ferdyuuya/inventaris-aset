<?php

namespace App\Livewire;

use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Procurement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AssetGenerationService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Computed;
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

    #[Validate('required|integer|min:1')]
    public $quantity = '';
    
    #[Validate('required|numeric|min:0')]
    public $unit_price = '';
    
    #[Validate('required|numeric|min:0')]
    public $total_cost = '';

    #[Validate('required|exists:locations,id')]
    public $location_id = '';

    // Component state
    public $selectedProcurementId = null;
    public $isEditing = false;
    public $showForm = false;
    public $showEditModal = false;
    public $search = '';
    public $showConfirmLocationModal = false;
    public $showConfirmQuantityModal = false;
    public $sortField = 'procurement_date';
    public $sortOrder = 'desc';
    public $perPage = 10;

    public function mount()
    {
        $this->resetForm();
        // Set current date as default
        if (!$this->procurement_date) {
            $this->procurement_date = now()->format('Y-m-d');
        }
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

    #[Computed]
    public function procurements()
    {
        return Procurement::query()
            ->select('procurements.*')
            ->with([
                'supplier:id,name',
                'category:id,name',
                'creator:id,name',
                'location:id,name'
            ])
            ->when($this->search, function($query) {
                $query->where('name', 'like', "%{$this->search}%")
                      ->orWhere('invoice_number', 'like', "%{$this->search}%")
                      ->orWhereHas('supplier', function($q) {
                          $q->where('name', 'like', "%{$this->search}%");
                      });
            })
            ->orderBy($this->sortField, $this->sortOrder)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.procurement-manager', [
            'suppliers' => $this->suppliers,
            'categories' => $this->categories,
            'locations' => $this->locations,
            'sortField' => $this->sortField,
            'sortOrder' => $this->sortOrder,
            'perPage' => $this->perPage,
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function suppliers()
    {
        return Supplier::select('id', 'name')->orderBy('name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function categories()
    {
        return AssetCategory::select('id', 'name')->orderBy('name')->get();
    }

    #[\Livewire\Attributes\Computed]
    public function locations()
    {
        return Location::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Calculate total cost based on quantity and unit price
     */
    public function updated($property)
    {
        if (in_array($property, ['quantity', 'unit_price'])) {
            $this->calculateTotalCost();
        }
    }

    public function calculateTotalCost()
    {
        if (!empty($this->quantity) && !empty($this->unit_price)) {
            $this->total_cost = $this->quantity * $this->unit_price;
        } else {
            $this->total_cost = 0;
        }
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->procurement_date = now()->format('Y-m-d');
        $this->isEditing = false;
    }

    public function edit($procurementId)
    {
        $procurement = Procurement::find($procurementId);
        
        if (!$procurement) return;
        
        $this->selectedProcurementId = $procurement->id;
        $this->name = $procurement->name;
        $this->asset_category_id = $procurement->asset_category_id;
        $this->location_id = $procurement->location_id;
        $this->supplier_id = $procurement->supplier_id;
        $this->procurement_date = $procurement->procurement_date->format('Y-m-d');
        $this->invoice_number = $procurement->invoice_number;
        $this->quantity = $procurement->quantity;
        $this->unit_price = $procurement->unit_price;
        $this->total_cost = $procurement->quantity * $procurement->unit_price;
        
        $this->isEditing = true;
        $this->showEditModal = true;
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateProcurement();
        } else {
            // Show confirmation modal for quantity warning
            $this->showConfirmQuantityModal = true;
        }
    }

    public function confirmCreateProcurement()
    {
        $this->createProcurement();
        $this->showConfirmQuantityModal = false;
    }

    public function createProcurement()
    {
        $this->validate();

        try {
            // Create procurement record
            $procurement = Procurement::create([
                'name' => $this->name,
                'asset_category_id' => $this->asset_category_id,
                'location_id' => $this->location_id,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => $this->procurement_date,
                'invoice_number' => $this->invoice_number ?: null,
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price,
                'total_cost' => $this->quantity * $this->unit_price,
                'created_by' => Auth::id(),
            ]);

            // Auto-generate assets
            $assetService = new AssetGenerationService();
            $assetsCreated = $assetService->generateAssets([
                'name' => $this->name,
                'asset_category_id' => $this->asset_category_id,
                'location_id' => $this->location_id,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => \Carbon\Carbon::parse($this->procurement_date),
                'invoice_number' => $this->invoice_number,
                'quantity' => (int)$this->quantity,
                'unit_price' => (float)$this->unit_price,
            ]);

            $this->resetForm();
            $this->showForm = false;
            $this->modal('createProcurement')->close();
            $this->dispatch('procurement-created');
            session()->flash('message', "Procurement created successfully with {$assetsCreated} assets generated.");
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating procurement: ' . $e->getMessage());
        }
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
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_cost' => $this->quantity * $this->unit_price, // Recalculate total cost
            // 'total_cost' => $this->total_cost,
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
        $this->location_id = '';
        $this->supplier_id = '';
        $this->procurement_date = now()->format('Y-m-d');
        $this->invoice_number = '';
        $this->quantity = '';
        $this->unit_price = '';
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
