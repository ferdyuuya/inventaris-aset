<?php

namespace App\Services\Export;

use App\Helpers\ExportSanitizer;
use App\Models\AssetLoan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * LoanExportService
 * 
 * Handles PDF export for asset loan/borrowing records.
 * Accepts filters and generates downloadable PDF report.
 */
class LoanExportService
{
    /**
     * Export loan records to PDF
     *
     * @param Carbon|null $dateFrom Start date filter
     * @param Carbon|null $dateTo End date filter
     * @param string|null $status Status filter (dipinjam, selesai, hilang)
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportToPdf(?Carbon $dateFrom = null, ?Carbon $dateTo = null, ?string $status = null)
    {
        $loans = $this->buildQuery($dateFrom, $dateTo, $status);
        
        // Sanitize all text fields to ensure valid UTF-8
        $loans = ExportSanitizer::sanitizeCollection($loans);
        
        $pdf = Pdf::loadView('exports.pdf.loan', [
            'loans' => $loans,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'status' => $status,
            'generatedAt' => now(),
            'title' => 'Asset Loan / Borrowing Report',
        ]);

        $pdf->setPaper('a4', 'landscape');
        
        $filename = 'loan-report-' . now()->format('Y-m-d-His') . '.pdf';
        
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
        return AssetLoan::query()
            ->with([
                'asset:id,asset_code,name',
                'borrower:id,name,nik,position',
            ])
            ->when($dateFrom, fn($q) => $q->whereDate('loan_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('loan_date', '<=', $dateTo))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('loan_date', 'asc')
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
            'dipinjam' => 'On Loan',
            'selesai' => 'Returned',
            'hilang' => 'Lost',
        ];
    }
}
