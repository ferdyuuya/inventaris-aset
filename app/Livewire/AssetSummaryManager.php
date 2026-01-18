<?php

namespace App\Livewire;

use App\Models\Asset;
use App\Services\AssetService;
use Livewire\Component;
use Livewire\Attributes\Computed;

class AssetSummaryManager extends Component
{
    /**
     * Get summary metrics
     */
    #[Computed]
    public function metrics()
    {
        return app(AssetService::class)->getSummaryMetrics();
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
