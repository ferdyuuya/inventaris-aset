<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Services\AssetService;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class AssetSummaryManager extends Component
{
    use WithPagination;

    public int $perPage = 10;
    public string $sortField = 'asset_code';
    public string $sortOrder = 'desc';
    public string $search = '';

    /**
     * Get summary metrics
     */
    #[Computed]
    public function metrics()
    {
        return app(AssetService::class)->getSummaryMetrics();
    }

    /**
     * Get filtered and paginated assets
     */
    #[Computed]
    public function assets()
    {
        $query = Asset::query()
            ->with(['category', 'location', 'supplier']);

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
     * Refresh metrics (cache invalidation)
     */
    public function refresh(): void
    {
        app(AssetService::class)->invalidateSummaryCache();
        $this->dispatch('notify', 'Metrics refreshed successfully');
    }

    public function render()
    {
        return view('livewire.asset-summary-manager', [
            'metrics' => $this->metrics,
            'assets' => $this->assets,
        ]);
    }
}
