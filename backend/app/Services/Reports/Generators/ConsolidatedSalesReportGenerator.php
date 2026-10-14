<?php

namespace App\Services\Reports\Generators;

use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

class ConsolidatedSalesReportGenerator
{
    public function __construct(protected ReportController $reportController) {}

    /**
     * Builds the Consolidated Sales Report PDF for Angkong Noodle House.
     * This only knows its own blade path, engine and layout — the actual
     * rendering / recording is handled by the universal report pipeline
     * (ReportController::generatePdf -> ReportService::pdfGenerationService).
     *
     * @param  string  $title  e.g. "Consolidated Sales Report - Sept 2026"
     * @param  array   $rows   Sales rows already pulled from the stored procedure.
     */
    public function generate(string $title, array $rows): array
    {
        $request = Request::create('/api/reports/pdf', 'POST', [
            'driver' => 'dompdf',
            'view' => 'reports.sales.consolidated',
            'title' => $title,
            'rows' => $rows,
            'paper_size' => 'Legal',
            'orientation' => 'landscape',
            'features' => ['non_editable'],
        ]);

        return $this->reportController->generatePdf($request)->getData(true);
    }
}
