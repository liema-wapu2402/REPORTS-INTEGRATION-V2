<?php

namespace App\Services\Reports;

use App\Services\Reports\Pdf\engines\DomPdfService;
use App\Services\Reports\Pdf\engines\SnappyPdfService;
use App\Services\Reports\Pdf\engines\PuppeteerPdfService;
use App\Services\Reports\Pdf\features\FillablePdfService;
use App\Services\Reports\Pdf\features\UploadablePdfService;
use App\Services\Reports\Writers\CsvWriter;
use App\Services\Reports\Writers\DocxWriter;
use App\Services\Reports\Writers\ExcelWriter;

class ReportService
{
    use RecordsReport;

    public function __construct(
        protected DomPdfService $domPdfService,
        protected SnappyPdfService $snappyPdfService,
        protected PuppeteerPdfService $puppeteerPdfService,
        protected FillablePdfService $fillablePdfService,
        protected UploadablePdfService $uploadablePdfService,
    ) {}

    /**
     * Takes the already-resolved payload from ReportController::generatePdf
     * (engine, features, layout, ...) and filters it down to the concrete
     * service(s) that actually build the file.
     */
    public function pdfGenerationService(array $payload): array
    {
        $engine = $payload['engine'];
        $features = $payload['features'];
        $layout = $payload['layout'];
        $filename = 'pdf-' . now()->format('Ymd-His') . '.pdf';

        // Fillable forms are built from a template, not a Blade view, so
        // they bypass the rendering engines entirely.
        if ($features['fillable']) {
            $path = $this->fillablePdfService->generate(
                $payload['template'],
                $payload['values'],
                $filename,
                $features['non_editable'] // flatten the form when also non-editable
            );
        } else {
            if ($engine === 'dompdf') {
                $engineService = $this->domPdfService;
            } elseif ($engine === 'snappy') {
                $engineService = $this->snappyPdfService;
            } elseif ($engine === 'puppeteer') {
                $engineService = $this->puppeteerPdfService;
            } else {
                $engineService = $this->domPdfService;
            }

            $path = $engineService->generate(
                $payload['view'],
                ($payload['meta'] ?? []) + [
                    'title' => $payload['title'],
                    'rows' => $payload['rows'],
                    'generated_at' => now()->toDayDateTimeString(),
                ],
                $filename,
                $layout
            );
        }

        // Uploadable attaches a user-submitted file to whatever was just built.
        if ($features['uploadable'] && $payload['upload']) {
            $path = $this->uploadablePdfService->attach($path, $payload['upload']);
        }

        $this->recordReport('pdf', $payload['title'], $path, $engine, [
            'layout' => $layout,
            'features' => $features,
            'row_count' => count($payload['rows']),
        ]);

        return [
            'message' => 'PDF generated.',
            'driver' => $engine,
            'path' => $path,
            'download_url' => route('reports.download', ['filename' => basename($path)]),
        ];
    }

    /**
     * payload: title, headings[], rows[][] (or assoc rows), totals[] (optional)
     */
    public function excelGenerationService(array $payload): array
    {
        $title = $payload['title'] ?? 'Excel Report';
        [$headings, $rows] = $this->normalizeTable($payload);
        $filename = 'excel-' . now()->format('Ymd-His') . '.xlsx';

        $path = (new ExcelWriter())->write([
            ['title' => $title, 'headings' => $headings, 'rows' => $rows, 'totals' => $payload['totals'] ?? []],
        ], $filename);

        $this->recordReport('excel', $title, $path, null, ['row_count' => count($rows)]);

        return $this->fileResponse('Excel workbook generated.', $path);
    }

    public function csvGenerationService(array $payload): array
    {
        $title = $payload['title'] ?? 'CSV Report';
        [$headings, $rows] = $this->normalizeTable($payload);
        $filename = 'csv-' . now()->format('Ymd-His') . '.csv';

        $path = (new CsvWriter())->write($headings, $rows, $filename);
        $this->recordReport('csv', $title, $path, null, ['row_count' => count($rows)]);

        return $this->fileResponse('CSV generated.', $path);
    }

    /**
     * payload: title, subtitle, paragraphs[], headings[]/rows[] (optional table)
     */
    public function docxGenerationService(array $payload): array
    {
        $title = $payload['title'] ?? 'Word Report';
        [$headings, $rows] = $this->normalizeTable($payload);
        $filename = 'docx-' . now()->format('Ymd-His') . '.docx';

        $path = (new DocxWriter())->write([
            'title' => $title,
            'subtitle' => $payload['subtitle'] ?? null,
            'meta' => ['Generated' => now()->toDayDateTimeString()],
            'paragraphs' => $payload['paragraphs'] ?? [],
            'table' => ['headings' => $headings, 'rows' => $rows],
        ], $filename);

        $this->recordReport('docx', $title, $path, null, ['row_count' => count($rows)]);

        return $this->fileResponse('Word document generated.', $path);
    }

    /**
     * Accepts either assoc rows ([{name:..,score:..}]) or list rows with an
     * explicit headings[] and returns [headings, list-rows].
     */
    protected function normalizeTable(array $payload): array
    {
        $rows = array_values($payload['rows'] ?? []);
        $headings = $payload['headings'] ?? [];

        if ($rows && ! $headings && array_is_list($rows) && is_array($rows[0]) && ! array_is_list($rows[0])) {
            $headings = array_keys($rows[0]);
        }

        $rows = array_map(fn ($r) => array_values((array) $r), $rows);

        return [$headings, $rows];
    }

    protected function fileResponse(string $message, string $path): array
    {
        return [
            'message' => $message,
            'path' => $path,
            'filename' => basename($path),
            'size_bytes' => is_file($path) ? filesize($path) : 0,
            'download_url' => route('reports.download', ['filename' => basename($path)]),
        ];
    }
}
