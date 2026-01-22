<?php

namespace App\Livewire\Procurements;

use App\Models\Procurement;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class ProcurementDetail extends Component
{
    use WithPagination, WithFileUploads;

    // Procurement ID
    public $procurementId = null;

    // Editable form properties
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('required|exists:suppliers,id')]
    public $supplier_id = '';

    #[Validate('required|date')]
    public $procurement_date = '';

    #[Validate('required|string|max:255')]
    public $invoice_number = '';

    // Read-only properties (stored but not editable)
    public $asset_category_id = '';
    public $location_id = '';
    public $quantity = '';
    public $unit_price = '';
    public $total_cost = '';

    // Document properties
    public $documents = [];
    public $temporaryDocuments = [];

    // Component state
    public $showEditModal = false;
    public $isEditing = false;
    public $assetsPerPage = 10;

    /**
     * Mount the component
     */
    public function mount($id)
    {
        $procurement = Procurement::findOrFail($id);
        $this->procurementId = $id;
        $this->loadProcurementData($procurement);
    }

    /**
     * Load procurement data into form properties
     */
    private function loadProcurementData(Procurement $procurement)
    {
        $this->name = $procurement->name;
        $this->supplier_id = $procurement->supplier_id;
        $this->procurement_date = $procurement->procurement_date->format('Y-m-d');
        $this->invoice_number = $procurement->invoice_number ?? '';

        // Read-only fields
        $this->asset_category_id = $procurement->asset_category_id;
        $this->location_id = $procurement->location_id;
        $this->quantity = $procurement->quantity;
        $this->unit_price = $procurement->unit_price;
        $this->total_cost = $procurement->total_cost;
        $this->documents = $procurement->documents ?? [];
    }

    /**
     * Open edit modal
     */
    public function openEditModal()
    {
        $this->isEditing = true;
        $this->showEditModal = true;
    }

    /**
     * Update procurement (only editable fields)
     */
    public function updateProcurement()
    {
        // Validate only editable fields
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'procurement_date' => 'required|date',
            'invoice_number' => 'required|string|max:255',
        ]);

        try {
            $procurement = Procurement::findOrFail($this->procurementId);

            // Store temporary files if any
            $documentPaths = $this->documents;
            if (count($this->temporaryDocuments) > 0) {
                foreach ($this->temporaryDocuments as $file) {
                    $path = $file->store('procurements', 'public');
                    $documentPaths[] = $path;
                }
            }

            // Update only editable fields
            $procurement->update([
                'name' => $this->name,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => $this->procurement_date,
                'invoice_number' => $this->invoice_number,
                'documents' => array_slice($documentPaths, 0, 3), // Limit to 3 documents
            ]);

            // Reset and close modal
            $this->temporaryDocuments = [];
            $this->showEditModal = false;
            $this->isEditing = false;
            $this->loadProcurementData($procurement);

            session()->flash('message', 'Procurement updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating procurement: ' . $e->getMessage());
        }
    }

    /**
     * Remove document by index
     */
    public function removeDocument($index)
    {
        if (isset($this->documents[$index])) {
            // Delete from storage if needed
            if (Storage::disk('public')->exists($this->documents[$index])) {
                Storage::disk('public')->delete($this->documents[$index]);
            }
            unset($this->documents[$index]);
            $this->documents = array_values($this->documents); // Re-index array
        }

        if (isset($this->temporaryDocuments[$index])) {
            unset($this->temporaryDocuments[$index]);
            $this->temporaryDocuments = array_values($this->temporaryDocuments);
        }
    }

    /**
     * Close edit modal and reset form
     */
    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->isEditing = false;
        $this->temporaryDocuments = [];
        $this->loadProcurementData(Procurement::findOrFail($this->procurementId));
    }

    /**
     * Get procurement data
     */
    public function getProcurement()
    {
        return Procurement::findOrFail($this->procurementId);
    }

    /**
     * Get related assets with pagination
     */
    #[\Livewire\Attributes\Computed]
    public function assets()
    {
        return Procurement::findOrFail($this->procurementId)
            ->assets()
            ->paginate($this->assetsPerPage);
    }

    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.procurements.procurement-detail', [
            'procurement' => $this->getProcurement(),
            'suppliers' => Supplier::all(),
            'categories' => AssetCategory::all(),
            'locations' => Location::all(),
        ]);
    }
}
