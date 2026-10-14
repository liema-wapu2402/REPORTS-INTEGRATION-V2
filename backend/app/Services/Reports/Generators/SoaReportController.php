<?php

namespace App\Services\Reports\Generators;

use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

class SoaReportController
{
    public function __construct(protected ReportController $reportController) {}

    /**
     * Builds the ESOA (Statement of Account) PDF. Uses the puppeteer
     * engine since the ESOA blade template relies on Browsershot for its
     * border rendering and multi-page overflow layout.
     *
     * @param  string  $title  e.g. "Statement of Account - <patient/member>"
     * @param  array   $rows   ESOA line items already assembled upstream.
     */
    public function generate(string $title, array $rows): array
    {
        $request = Request::create('/api/reports/pdf', 'POST', [
            'driver' => 'puppeteer',
            'view' => 'reports.claims.esoa',
            'title' => $title,
            'rows' => $rows,
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'features' => ['non_editable'],
        ]);

        return $this->reportController->generatePdf($request)->getData(true);
    }
}
