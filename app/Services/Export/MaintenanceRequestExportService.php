<?php

namespace App\Services\Export;

use App\Helpers\ExportSanitizer;
use App\Models\MaintenanceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * MaintenanceRequestExportService
 * 
 * Handles PDF export for maintenance request records.
 * Accepts filters and generates downloadable PDF report.
 */
class MaintenanceRequestExportService
{
    /**
     * Export maintenance request records to PDF
     *
     * @param Carbon|null $dateFrom Start date filter
     * @param Carbon|null $dateTo End date filter
     * @param string|null $status Status filter (pending, approved, rejected)
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToPdf(?Carbon $dateFrom = null, ?Carbon $dateTo = null, ?string $status = null)
    {
        $requests = $this->buildQuery($dateFrom, $dateTo, $status);
        
        // Sanitize all text fields to ensure valid UTF-8
        $requests = ExportSanitizer::sanitizeCollection($requests);
        
        $pdf = Pdf::loadView('exports.pdf.maintenance-request', [
            'requests' => $requests,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'generatedAt' => now(),
            'title' => 'Maintenance Requests Report',
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'maintenance-requests-report-' . now()->format('Y-m-d-His') . '.pdf';
        
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
        return MaintenanceRequest::query()
            ->with([
                'asset:id,asset_code,name',
                'requester:id,name',
                'approver:id,name',
            ])
            ->when($dateFrom, fn($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'asc')
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
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }
}
