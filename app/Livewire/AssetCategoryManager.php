<?php

namespace App\Livewire;

use App\Models\AssetCategory;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class AssetCategoryManager extends Component
{
    use WithPagination;

    // Form properties
    #[Validate('required|string|max:255|unique:asset_categories,name')]
    public $name = '';
    
    #[Validate('nullable|string')]
    public $description = '';

    // Component state
    public $selectedCategoryId = null;
    public $isEditing = false;
    public $categoryToDelete = null;
    public $search = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function render()
    {
        $categories = AssetCategory::when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.asset-category-manager', [
            'categories' => $categories,
        ]);
    }

    public function showCreateForm()
    {
        $this->resetForm();
        $this->modal('createCategory')->show();
    }

    public function showEditForm($categoryId)
    {
        $category = AssetCategory::findOrFail($categoryId);
        
        $this->selectedCategoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isEditing = true;
        
        $this->modal('editCategory')->show();
    }

    public function save()
    {
        if ($this->isEditing) {
            $this->updateCategory();
        } else {
            $this->createCategory();
        }
    }

    public function createCategory()
    {
        $this->validate();

        AssetCategory::create([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->modal('createCategory')->close();
        $this->resetForm();
        session()->flash('message', 'Asset category created successfully.');
    }

    public function updateCategory()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:asset_categories,name,' . $this->selectedCategoryId,
            'description' => 'nullable|string',
        ];

        $this->validate($rules);

        $category = AssetCategory::findOrFail($this->selectedCategoryId);
        $category->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->modal('editCategory')->close();
        $this->resetForm();
        session()->flash('message', 'Asset category updated successfully.');
    }

    public function showDeleteConfirmation($categoryId)
    {
        $this->categoryToDelete = AssetCategory::findOrFail($categoryId);
        $this->modal('deleteCategory')->show();
    }

    public function confirmDelete()
    {
        if ($this->categoryToDelete) {
            $this->categoryToDelete->delete();
            $this->modal('deleteCategory')->close();
            $this->categoryToDelete = null;
            session()->flash('message', 'Asset category deleted successfully.');
        }
    }

    public function resetForm()
    {
        $this->name = '';
        $this->description = '';
        $this->selectedCategoryId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
    }
}
