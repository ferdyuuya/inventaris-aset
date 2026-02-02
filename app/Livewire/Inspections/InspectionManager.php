<?php

namespace App\Livewire\Inspections;

use App\Models\Asset;
use App\Models\Inspection;
use App\Services\InspectionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

/**
 * InspectionManager Livewire Component
 * 
 * Handles the inspection index page with:
 * - Listing all inspections
 * - Search functionality
 * - View inspection detail modal
 * - Create inspection modal (for any asset)
 * 
 * All business logic is delegated to InspectionService.
 */
class InspectionManager extends Component
{
    use WithPagination;

    public int $perPage = 25;
    public string $search = '';

    // Modal states
    public bool $showViewModal = false;
    public bool $showCreateModal = false;
    public ?Inspection $selectedInspection = null;

    // Create inspection form
    public ?int $createAssetId = null;
    public string $createCondition = '';
    public string $createDescription = '';

    /**
     * Get all inspections with pagination
     */
    #[Computed]
    public function inspections()
    {
        return app(InspectionService::class)->getAllInspections(
            $this->search ?: null,
            $this->perPage
        );
    }

    /**
     * Get available assets for inspection (non-disposed only)
     */
    #[Computed]
    public function availableAssets()
    {
        return Asset::notDisposed()
            ->orderBy('asset_code')
            ->get(['id', 'asset_code', 'name', 'condition']);
    }

    /**
     * Open view modal
     */
    public function viewInspection(Inspection $inspection): void
    {
        $this->selectedInspection = $inspection->load('asset', 'inspector');
        $this->showViewModal = true;
    }

    /**
     * Close view modal
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->selectedInspection = null;
    }

    /**
     * Open create modal
     */
    public function openCreateModal(?int $assetId = null): void
    {
        $this->resetCreateForm();
        $this->createAssetId = $assetId;
        $this->showCreateModal = true;
    }

    /**
     * Close create modal
     */
    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    /**
     * Reset create form
     */
    public function resetCreateForm(): void
    {
        $this->createAssetId = null;
        $this->createCondition = '';
        $this->createDescription = '';
        $this->resetValidation();
    }

    /**
     * Submit create inspection
     * 
     * Delegates to InspectionService for atomic execution.
     */
    public function submitCreateInspection(): void
    {
        $this->validate([
            'createAssetId' => 'required|integer|exists:assets,id',
            'createCondition' => 'required|in:baik,rusak,perlu_perbaikan',
            'createDescription' => 'nullable|string|max:1000',
        ]);

        try {
            $user = Auth::user();

            if (!$user) {
                $this->dispatch('notify', type: 'error', message: 'You must be logged in to perform inspections.');
                return;
            }

            $asset = Asset::findOrFail($this->createAssetId);

            // Delegate to service (handles atomic transaction)
            app(InspectionService::class)->createInspection(
                $asset,
                $this->createCondition,
                $this->createDescription ?: null,
                $user
            );

            $this->dispatch('notify', type: 'success', message: 'Inspection recorded successfully. Asset condition updated.');
            $this->closeCreateModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Inspection failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete an inspection (optional admin action)
     */
    public function deleteInspection(Inspection $inspection): void
    {
        try {
            app(InspectionService::class)->deleteInspection($inspection);
            $this->dispatch('notify', type: 'success', message: 'Inspection record deleted.');
            $this->closeViewModal();
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Failed to delete inspection: ' . $e->getMessage());
        }
    }

    /**
     * Export inspections to PDF
     */
    public function exportPdf()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            $this->dispatch('notify', type: 'error', message: 'Only administrators can export reports.');
            return;
        }

        return $this->redirect(route('export.inspection'), navigate: false);
    }

    public function render()
    {
        return view('livewire.inspections.inspection-manager', [
            'inspections' => $this->inspections,
            'availableAssets' => $this->availableAssets,
        ]);
    }
}
