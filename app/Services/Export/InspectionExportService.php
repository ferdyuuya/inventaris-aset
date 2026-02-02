<?php

namespace App\Services\Export;

use App\Helpers\ExportSanitizer;
use App\Models\Inspection;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * InspectionExportService
 * 
 * Handles PDF export for inspection records.
 * Accepts filters and generates downloadable PDF report.
 */
class InspectionExportService
{
    /**
     * Export inspection records to PDF
     *
     * @param Carbon|null $dateFrom Start date filter
     * @param Carbon|null $dateTo End date filter
     * @param string|null $conditionAfter Condition filter
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToPdf(?Carbon $dateFrom = null, ?Carbon $dateTo = null, ?string $conditionAfter = null)
    {
        $inspections = $this->buildQuery($dateFrom, $dateTo, $conditionAfter);
        
        // Sanitize all text fields to ensure valid UTF-8
        $inspections = ExportSanitizer::sanitizeCollection($inspections);
        
        $pdf = Pdf::loadView('exports.pdf.inspection', [
            'inspections' => $inspections,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'conditionAfter' => $conditionAfter,
            'generatedAt' => now(),
            'title' => 'Asset Inspection Report',
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'inspection-report-' . now()->format('Y-m-d-His') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Build the query with filters
     *
     * @param Carbon|null $dateFrom
     * @param Carbon|null $dateTo
     * @param string|null $conditionAfter
     * @return Collection
     */
    protected function buildQuery(?Carbon $dateFrom, ?Carbon $dateTo, ?string $conditionAfter): Collection
    {
        return Inspection::query()
            ->with([
                'asset:id,asset_code,name',
                'inspector:id,name',
            ])
            ->when($dateFrom, fn($q) => $q->whereDate('inspected_at', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('inspected_at', '<=', $dateTo))
            ->when($conditionAfter, fn($q) => $q->where('condition_after', $conditionAfter))
            ->orderBy('inspected_at', 'asc')
            ->get();
    }

    /**
     * Get available condition options for filtering
     *
     * @return array
     */
    public static function getConditionOptions(): array
    {
        return [
            'baik' => 'Good',
            'rusak' => 'Damaged',
            'perlu_perbaikan' => 'Needs Repair',
        ];
    }
}
