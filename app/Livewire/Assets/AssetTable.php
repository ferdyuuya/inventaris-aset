<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Services\AssetService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AssetTable extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public string $filterCategory = '';
    public string $filterStatus = '';
    public string $filterLocation = '';
    public string $sortField = 'created_at';
    public string $sortOrder = 'desc';

    /**
     * Get filtered and paginated assets
     */
    #[Computed]
    public function assets()
    {
        $filters = [
            'search' => $this->search,
            'category_id' => $this->filterCategory,
            'status' => $this->filterStatus,
            'location_id' => $this->filterLocation,
        ];

        return app(AssetService::class)->getAssetList($filters, $this->perPage);
    }

    /**
     * Get all categories for filter dropdown
     */
    #[Computed]
    public function categories()
    {
        return AssetCategory::all();
    }

    /**
     * Get all locations for filter dropdown
     */
    #[Computed]
    public function locations()
    {
        return Location::all();
    }

    /**
     * Update per-page setting
     */
    public function updatePerPage(int $perPage): void
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    /**
     * Reset all filters and go to first page
     */
    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterCategory = '';
        $this->filterStatus = '';
        $this->filterLocation = '';
        $this->resetPage();
    }

    /**
     * Toggle sort order
     */
    public function toggleSort(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortOrder = 'desc';
        }
    }

    public function render()
    {
        return view('livewire.assets.asset-table', [
            'assets' => $this->assets,
            'categories' => $this->categories,
            'locations' => $this->locations,
        ]);
    }
}
