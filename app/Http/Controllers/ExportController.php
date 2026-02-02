<?php

namespace App\Http\Controllers;

use App\Services\Export\MaintenanceExportService;
use App\Services\Export\MaintenanceRequestExportService;
use App\Services\Export\InspectionExportService;
use App\Services\Export\LoanExportService;
use App\Services\Export\ProcurementExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * ExportController
 * 
 * Handles PDF export requests for various modules.
 * Admin-only access.
 */
class ExportController extends Controller
{
    /**
     * Export maintenance records to PDF
     *
     * @param Request $request
     * @param MaintenanceExportService $service
     * @return \Illuminate\Http\Response
     */
    public function exportMaintenance(Request $request, MaintenanceExportService $service)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:dalam_proses,selesai,dibatalkan',
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to']) : null;
        $status = $validated['status'] ?? null;

        return $service->exportToPdf($dateFrom, $dateTo, $status);
    }

    /**
     * Export maintenance request records to PDF
     *
     * @param Request $request
     * @param MaintenanceRequestExportService $service
     * @return \Illuminate\Http\Response
     */
    public function exportMaintenanceRequest(Request $request, MaintenanceRequestExportService $service)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:pending,approved,rejected',
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to']) : null;
        $status = $validated['status'] ?? null;

        return $service->exportToPdf($dateFrom, $dateTo, $status);
    }

    /**
     * Export inspection records to PDF
     *
     * @param Request $request
     * @param InspectionExportService $service
     * @return \Illuminate\Http\Response
     */
    public function exportInspection(Request $request, InspectionExportService $service)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'condition_after' => 'nullable|string|in:baik,rusak,perlu_perbaikan',
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to']) : null;
        $conditionAfter = $validated['condition_after'] ?? null;

        return $service->exportToPdf($dateFrom, $dateTo, $conditionAfter);
    }

    /**
     * Export loan records to PDF
     *
     * @param Request $request
     * @param LoanExportService $service
     * @return \Illuminate\Http\Response
     */
    public function exportLoan(Request $request, LoanExportService $service)
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|string|in:dipinjam,selesai,hilang',
        ]);

        $dateFrom = isset($validated['date_from']) ? Carbon::parse($validated['date_from']) : null;
        $dateTo = isset($validated['date_to']) ? Carbon::parse($validated['date_to']) : null;
        $status = $validated['status'] ?? null;

        return $service->exportToPdf($dateFrom, $dateTo, $status);
    }

    /**
     * Export procurement records to PDF by year
     *
     * @param Request $request
     * @param ProcurementExportService $service
     * @return \Illuminate\Http\Response
     */
    public function exportProcurement(Request $request, ProcurementExportService $service)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        return $service->exportToPdf((int) $validated['year']);
    }

    /**
     * Get available years for procurement export
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProcurementYears()
    {
        return response()->json([
            'years' => ProcurementExportService::getAvailableYears(),
        ]);
    }
}
