<?php

namespace App\Services\Export;

use App\Helpers\ExportSanitizer;
use App\Models\Procurement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;

/**
 * ProcurementExportService
 * 
 * Handles PDF export for procurement records by year.
 * Groups data by month within the selected year.
 */
class ProcurementExportService
{
    /**
     * Export procurement records to PDF by year
     *
     * @param int $year The year to export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToPdf(int $year)
    {
        $procurements = $this->buildQuery($year);
        
        // Sanitize all text fields to ensure valid UTF-8
        $procurements = ExportSanitizer::sanitizeCollection($procurements);
        
        // Group by month for the report
        $groupedByMonth = $procurements->groupBy(function ($procurement) {
            return $procurement->procurement_date->format('F'); // Month name
        });

        // Calculate totals
        $totalQuantity = $procurements->sum('quantity');
        $totalCost = $procurements->sum('total_cost');
        
        $pdf = Pdf::loadView('exports.pdf.procurement', [
            'procurements' => $procurements,
            'groupedByMonth' => $groupedByMonth,
            'year' => $year,
            'totalQuantity' => $totalQuantity,
            'totalCost' => $totalCost,
            'generatedAt' => now(),
            'title' => "Procurement Report - Year {$year}",
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = "procurement-report-{$year}-" . now()->format('Y-m-d-His') . '.pdf';
        
        return $pdf->stream($filename);
    }

    /**
     * Build the query for a specific year
     *
     * @param int $year
     * @return Collection
     */
    protected function buildQuery(int $year): Collection
    {
        return Procurement::query()
            ->with([
                'supplier:id,name',
                'category:id,name',
                'location:id,name',
                'creator:id,name',
                'assets:id,procurement_id,asset_code,name',
            ])
            ->whereYear('procurement_date', $year)
            ->orderBy('procurement_date', 'asc')
            ->get();
    }

    /**
     * Get available years for filtering (dynamically from data)
     *
     * @return array
     */
    public static function getAvailableYears(): array
    {
        $years = Procurement::selectRaw('DISTINCT YEAR(procurement_date) as year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // If no data, provide current year and previous 2 years
        if (empty($years)) {
            $currentYear = now()->year;
            $years = [$currentYear, $currentYear - 1, $currentYear - 2];
        }

        return $years;
    }
}
