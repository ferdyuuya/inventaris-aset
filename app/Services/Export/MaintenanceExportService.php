<?php

namespace App\Services\Export;

use App\Helpers\ExportSanitizer;
use App\Models\AssetMaintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * MaintenanceExportService
 * 
 * Handles PDF export for asset maintenance records.
 * Accepts filters and generates downloadable PDF report.
 */
class MaintenanceExportService
{
    /**
     * Export maintenance records to PDF
     *
     * @param Carbon|null $dateFrom Start date filter
     * @param Carbon|null $dateTo End date filter
     * @param string|null $status Status filter (dalam_proses, selesai, dibatalkan)
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToPdf(?Carbon $dateFrom = null, ?Carbon $dateTo = null, ?string $status = null)
    {
        $maintenances = $this->buildQuery($dateFrom, $dateTo, $status);
        
        // Sanitize all text fields to ensure valid UTF-8
        $maintenances = ExportSanitizer::sanitizeCollection($maintenances);
        
        $pdf = Pdf::loadView('exports.pdf.maintenance', [
            'maintenances' => $maintenances,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'generatedAt' => now(),
            'title' => 'Asset Maintenance Report',
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'maintenance-report-' . now()->format('Y-m-d-His') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Build the query with filters
     *
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @param string|null $status
     * @return Collection
     */
    protected function buildQuery(?Carbon $dateFrom, ?Carbon $dateTo, ?string $status): Collection
    {
        return AssetMaintenance::query()
            ->with([
                'asset:id,asset_code,name',
                'maintenanceRequest:id,issue_description,requested_by',
                'maintenanceRequest.requester:id,name',
                'pic:id,name,position',
                'creator:id,name',
            ])
            ->when($dateFrom, fn($q) => $q->whereDate('maintenance_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('maintenance_date', '<=', $dateTo))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('maintenance_date', 'asc')
            ->get();
    }

    /**
     * Get available status options for filtering
     *
     * @return array
     */
    public static function getStatusOptions(): array
    {
        return [
            'dalam_proses' => 'In Progress',
            'selesai' => 'Completed',
            'dibatalkan' => 'Cancelled',
        ];
    }
}
