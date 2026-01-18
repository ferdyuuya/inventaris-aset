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
        ]);
    }
}
