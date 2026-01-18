<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AssetsManager extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public string $sortField = 'asset_code';
    public string $sortOrder = 'desc';

    /**
     * Get filtered and paginated assets
     */
    #[Computed]
    public function assets()
    {
        $query = Asset::query()
            ->select('assets.*')
            ->with([
                'category:id,name',
                'location:id,name',
                'supplier:id,name'
            ]);

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('asset_code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%");
            });
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortOrder);

        return $query->paginate($this->perPage);
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

    /**
     * Update per-page setting
     */
    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.assets-manager', [
            'assets' => $this->assets,
        ]);
    }
}

