<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\Reports\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService) {}

    /**
     * GET /api/reports
     * GET /api/reports?type=pdf
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'in:' . implode(',', Report::TYPES),
        ]);

        $reports = Report::query()
            ->when($request->query('type'), fn ($q, $type) => $q->ofType($type))
            ->latest()
            ->paginate(25);

        return response()->json($reports);
    }

    /**
     * GET /api/reports/types
     */
    public function types()
    {
        $counts = Report::query()
            ->selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return response()->json(
            collect(Report::TYPES)->mapWithKeys(fn ($type) => [$type => $counts[$type] ?? 0])
        );
    }

    /**
     * GET /api/reports/download/{filename}
     * Streams a previously generated report back to the browser.
     * Every engine service (Dom/Snappy/Puppeteer) writes to the same
     * storage/app/reports directory, so the lookup is a single fixed path.
     */
    public function download(string $filename)
    {
        $path = storage_path('app/reports/' . basename($filename));

        abort_unless(file_exists($path), 404, 'Report not found.');

        return response()->download($path);
    }

    /**
     * POST /api/reports/pdf
     * body: {
     *   driver: dompdf|snappy|puppeteer,
     *   title, rows: [...],
     *   paper_size: A4|Letter|Legal, orientation: portrait|landscape,
     *   features: [fillable, uploadable, non_editable],
     *   sample / template, values, upload
     * }
     *
     * Single entry point for PDFs. This method only reads the request and
     * decides WHAT is needed (engine / features / layout) via conditionals,
     * then hands the resolved payload to ReportService::pdfGenerationService,
     * which decides WHICH service classes actually build the file.
     */
    public function generatePdf(Request $request)
    {
        $validated = $request->validate([
            'driver' => 'required|in:dompdf,snappy,puppeteer',
            'view' => ['nullable', 'string', 'regex:/^reports\.[A-Za-z0-9_.\-]+$/'],
            'meta' => 'nullable|array',
            'title' => 'required|string|max:255',
            'rows' => 'array',
            'paper_size' => 'in:A4,Letter,Legal',
            'orientation' => 'in:portrait,landscape',
            'features' => 'array',
            'features.*' => 'in:fillable,uploadable,non_editable',
            'sample' => 'nullable|string',
            'template' => 'nullable|string',
            'values' => 'nullable|array',
            'upload' => 'nullable|file',
        ]);

        // 1. Which rendering engine?
        if ($validated['driver'] === 'dompdf') {
            $engine = 'dompdf';
        } elseif ($validated['driver'] === 'snappy') {
            $engine = 'snappy';
        } elseif ($validated['driver'] === 'puppeteer') {
            $engine = 'puppeteer';
        } else {
            $engine = 'dompdf';
        }

        // 2. Which features are requested?
        $requested = $validated['features'] ?? [];

        $features = [
            'fillable' => in_array('fillable', $requested, true),
            'uploadable' => in_array('uploadable', $requested, true),
            'non_editable' => in_array('non_editable', $requested, true),
        ];

        // 3. Layout properties.
        $layout = [
            'paper' => strtolower($validated['paper_size'] ?? 'a4'),
            'orientation' => $validated['orientation'] ?? 'portrait',
        ];

        $payload = [
            'engine' => $engine,
            'view' => $validated['view'] ?? 'reports.sample',
            'title' => $validated['title'],
            'rows' => $validated['rows'] ?? [],
            'meta' => $validated['meta'] ?? [],
            'layout' => $layout,
            'features' => $features,
            'template' => $validated['template'] ?? $validated['sample'] ?? null,
            'values' => $validated['values'] ?? [],
            'upload' => $validated['upload'] ?? null,
        ];

        $result = $this->reportService->pdfGenerationService($payload);

        return response()->json($result);
    }

    /**
     * POST /api/reports/excel
     */
    public function generateExcel(Request $request)
    {
        $data = $request->validate($this->tableRules() + ['totals' => 'array']);

        return response()->json($this->reportService->excelGenerationService($data));
    }

    /**
     * POST /api/reports/csv
     */
    public function generateCsv(Request $request)
    {
        $data = $request->validate($this->tableRules());

        return response()->json($this->reportService->csvGenerationService($data));
    }

    /**
     * POST /api/reports/docx
     */
    public function generateDocx(Request $request)
    {
        $data = $request->validate($this->tableRules() + [
            'subtitle' => 'nullable|string|max:255',
            'paragraphs' => 'array',
            'paragraphs.*' => 'string',
        ]);

        return response()->json($this->reportService->docxGenerationService($data));
    }

    /**
     * DELETE /api/reports/{report}
     * Removes the history row and the file on disk.
     */
    public function destroy(Report $report)
    {
        $path = storage_path('app/reports/' . basename($report->filename));
        if (is_file($path)) {
            @unlink($path);
        }
        $report->delete();

        return response()->json(['message' => 'Report deleted.']);
    }

    private function tableRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'headings' => 'array',
            'headings.*' => 'string',
            'rows' => 'array',
        ];
    }
}
