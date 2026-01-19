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
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProcurementManager extends Component
{
    use WithPagination, WithFileUploads;

    // Form properties
    #[Validate('required|string|max:255')]
    public $name = '';
    
    #[Validate('required|exists:asset_categories,id')]
    public $asset_category_id = '';
    
    #[Validate('required|exists:suppliers,id')]
    public $supplier_id = '';
    
    #[Validate('required|date')]
    public $procurement_date = '';
    
    #[Validate('nullable|array|max:3')]
    public $documents = [];

    #[Validate('required|integer|min:1')]
    public $quantity = '';
    
    #[Validate('required|numeric|min:0')]
    public $unit_price = '';
    
    #[Validate('required|numeric|min:0')]
    public $total_cost = '';

    #[Validate('required|exists:locations,id')]
    public $location_id = '';

    // File upload properties
    public $temporaryDocuments = [];
    public $uploadedDocumentNames = [];

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
     * Handle incremental file uploads
     */
    public function updated($property)
    {
        if (in_array($property, ['quantity', 'unit_price'])) {
            $this->calculateTotalCost();
        }
        
        // Handle incremental file addition
        if ($property === 'temporaryDocuments') {
            $this->handleFileAddition();
        }
    }

    /**
     * Handle incremental file addition - append new files to existing ones
     */
    public function handleFileAddition(): void
    {
        // Only process if we have new files
        if (empty($this->temporaryDocuments)) {
            return;
        }

        // Get the count of new files added
        $newFilesCount = count($this->temporaryDocuments);
        
        // Validate total file count doesn't exceed 3
        $totalFiles = count($this->documents) + $newFilesCount;
        if ($totalFiles > 3) {
            // Remove the extra files beyond 3 total
            $this->temporaryDocuments = array_slice($this->temporaryDocuments, 0, 3 - count($this->documents));
            session()->flash('warning', 'Maximum 3 files allowed. Extra files have been removed.');
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
        $this->documents = $procurement->documents ?? [];
        $this->quantity = $procurement->quantity;
        $this->unit_price = $procurement->unit_price;
        $this->total_cost = $procurement->quantity * $procurement->unit_price;
        
        // Reset temporary files
        $this->temporaryDocuments = [];
        $this->uploadedDocumentNames = [];
        
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
        try {
            // Validate all fields
            $this->validate();
            
            // Validate documents
            $this->validateDocuments();

            // Store temporary files
            $documentPaths = $this->storeDocuments();
            
            // Create procurement record
            $procurement = Procurement::create([
                'name' => $this->name,
                'asset_category_id' => $this->asset_category_id,
                'location_id' => $this->location_id,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => $this->procurement_date,
                'documents' => $documentPaths,
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price,
                'total_cost' => $this->quantity * $this->unit_price,
                'created_by' => Auth::id(),
            ]);

            // Auto-generate assets
            $assetService = new AssetGenerationService();
            $assetsCreated = $assetService->generateAssets([
                'procurement_id' => $procurement->id,
                'name' => $this->name,
                'asset_category_id' => $this->asset_category_id,
                'location_id' => $this->location_id,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => \Carbon\Carbon::parse($this->procurement_date),
                'documents' => $documentPaths,
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
        try {
            // Validate all fields
            $this->validate();
            
            // Validate documents
            $this->validateDocuments();

            $procurement = Procurement::findOrFail($this->selectedProcurementId);
            
            // Store new temporary files
            $documentPaths = $this->storeDocuments();
            
            // Merge with existing documents if not replacing
            $finalDocuments = array_merge($this->documents, $documentPaths);

            $procurement->update([
                'name' => $this->name,
                'asset_category_id' => $this->asset_category_id,
                'supplier_id' => $this->supplier_id,
                'procurement_date' => $this->procurement_date,
                'documents' => $finalDocuments,
                'quantity' => $this->quantity,
                'unit_price' => $this->unit_price,
                'total_cost' => $this->quantity * $this->unit_price,
            ]);

            $this->resetForm();
            $this->showForm = false;
            $this->modal('editProcurement')->close();
            $this->dispatch('procurement-updated');
            session()->flash('message', 'Procurement updated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating procurement: ' . $e->getMessage());
        }
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
        $this->documents = [];
        $this->temporaryDocuments = [];
        $this->uploadedDocumentNames = [];
        $this->quantity = '';
        $this->unit_price = '';
        $this->total_cost = '';
        $this->selectedProcurementId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
        $this->dispatch('fileInputReset');
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'procurement_date' => 'required|date',
            'temporaryDocuments.*' => 'nullable|file|mimes:pdf|max:2048',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'total_cost' => 'required|numeric|min:0',
            'location_id' => 'required|exists:locations,id',
        ];
    }

    /**
     * Validate documents - PDF only, max 2MB per file, max 3 files
     */
    public function validateDocuments(): void
    {
        $totalFiles = count($this->documents) + count($this->temporaryDocuments);
        
        if ($totalFiles > 3) {
            throw new \Exception('Maximum 3 documents allowed per procurement. You have ' . $totalFiles . ' files.');
        }

        foreach ($this->temporaryDocuments as $document) {
            // Check if file exists and is a valid upload
            if (!is_object($document)) {
                continue;
            }

            // Validate file size (2MB = 2048KB = 2097152 bytes)
            if ($document->getSize() > 2097152) {
                throw new \Exception('File "' . $document->getClientOriginalName() . '" exceeds 2MB limit. Size: ' . round($document->getSize() / 1024 / 1024, 2) . 'MB');
            }

            // Validate file extension
            $extension = strtolower($document->getClientOriginalExtension());
            if ($extension !== 'pdf') {
                throw new \Exception('File "' . $document->getClientOriginalName() . '" is not a PDF. Only PDF files are allowed.');
            }
        }
    }

    /**
     * Store temporary documents to storage
     */
    public function storeDocuments(): array
    {
        $paths = [];

        foreach ($this->temporaryDocuments as $document) {
            // Create directory if it doesn't exist
            $directory = 'procurements/' . ($this->selectedProcurementId ?? 'new');
            
            // Store with original name
            $path = $document->store($directory);
            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Remove a document from the list
     */
    public function removeDocument($index): void
    {
        if ($this->isEditing) {
            // Remove from existing documents
            $this->documents = array_values(array_filter(
                $this->documents,
                fn($key) => $key !== $index,
                ARRAY_FILTER_USE_KEY
            ));
        } else {
            // Remove from temporary documents
            unset($this->temporaryDocuments[$index]);
            $this->temporaryDocuments = array_values($this->temporaryDocuments);
        }
    }
}
