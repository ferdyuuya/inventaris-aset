<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AssetIndex extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortOrder = 'desc';
    
    // Filters
    public ?int $filterLocation = null;
    public ?int $filterCategory = null;
    public ?string $filterStatus = null;

    /**
     * Get all locations for filter dropdown
     */
    #[Computed]
    public function locations()
    {
        return Location::orderBy('name')->get();
    }

    /**
     * Get all categories for filter dropdown
     */
    #[Computed]
    public function categories()
    {
        return AssetCategory::orderBy('name')->get();
    }

    /**
     * Get all status options
     */
    #[Computed]
    public function statuses()
    {
        return [
            'aktif' => 'Active',
            'dipinjam' => 'Borrowed',
            'dipelihara' => 'Maintenance',
            'nonaktif' => 'Inactive',
        ];
    }

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

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('asset_code', 'like', "%{$this->search}%")
                  ->orWhere('name', 'like', "%{$this->search}%");
            });
        }

        // Location filter
        if ($this->filterLocation) {
            $query->where('location_id', $this->filterLocation);
        }

        // Category filter
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        // Status filter
        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        // Sorting
        $query->orderBy($this->sortField, $this->sortOrder);

        return $query->paginate($this->perPage);
    }

    /**
     * Toggle sort direction or change sort field
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
     * Set sort field and direction
     */
    public function setSortField(string $field, string $direction = 'asc'): void
    {
        $this->sortField = $field;
        $this->sortOrder = $direction;
        $this->resetPage();
    }

    /**
     * Clear all filters
     */
    public function clearFilters(): void
    {
        $this->filterLocation = null;
        $this->filterCategory = null;
        $this->filterStatus = null;
        $this->search = '';
        $this->resetPage();
    }

    /**
     * Updated hooks - reset page on filter changes
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterLocation(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
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

    /**
     * Check if any filters are active
     */
    public function hasActiveFilters(): bool
    {
        return $this->filterLocation !== null
            || $this->filterCategory !== null
            || $this->filterStatus !== null;
    }

    public function render()
    {
        return view('livewire.assets.asset-index', [
            'assets' => $this->assets,
            'locations' => $this->locations,
            'categories' => $this->categories,
            'statuses' => $this->statuses,
            'hasActiveFilters' => $this->hasActiveFilters(),
        ]);
    }
}

