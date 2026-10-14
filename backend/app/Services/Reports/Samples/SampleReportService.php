<?php

namespace App\Services\Reports\Samples;

use App\Services\Reports\Pdf\engines\DomPdfService;
use App\Services\Reports\Pdf\engines\PuppeteerPdfService;
use App\Services\Reports\Pdf\engines\SnappyPdfService;
use App\Services\Reports\RecordsReport;
use App\Services\Reports\Writers\CsvWriter;
use App\Services\Reports\Writers\DocxWriter;
use App\Services\Reports\Writers\ExcelWriter;
use InvalidArgumentException;

/**
 * Turns a catalog entry into a real file using the matching generator,
 * records it in the `reports` table and returns the download info.
 */
class SampleReportService
{
    use RecordsReport;

    public function __construct(
        protected DomPdfService $dompdf,
        protected SnappyPdfService $snappy,
        protected PuppeteerPdfService $puppeteer,
        protected ExcelWriter $excel,
        protected CsvWriter $csv,
        protected DocxWriter $docx,
    ) {}

    /** Rows + headings for the on-screen preview (first $limit rows). */
    public function preview(array $sample, int $limit = 10): array
    {
        [$headings, $rows] = SampleData::table($sample['dataset']);

        return [
            'headings' => $headings,
            'rows' => array_slice($rows, 0, $limit),
            'total_rows' => count($rows),
        ];
    }

    /** @param array{paper?:string,orientation?:string} $options PDF layout overrides */
    public function generate(string $id, array $options = []): array
    {
        $sample = SampleCatalog::find($id) ?? throw new InvalidArgumentException("Unknown sample [$id].");

        $started = microtime(true);
        $stamp = now()->format('Ymd-His');
        [$headings, $rows] = SampleData::table($sample['dataset']);

        $path = match ($sample['format']) {
            'pdf' => $this->pdf($sample, $rows, "{$sample['id']}-$stamp.pdf", $options),
            'excel' => $this->xlsx($sample, "{$sample['id']}-$stamp.xlsx"),
            'csv' => $this->csv->write($headings, $rows, "{$sample['id']}-$stamp.csv"),
            'docx' => $this->word($sample, $headings, $rows, "{$sample['id']}-$stamp.docx"),
        };

        $elapsed = (int) round((microtime(true) - $started) * 1000);

        $this->recordReport($sample['format'], $sample['name'], $path, $sample['format'] === 'pdf' ? $sample['generator'] : null, [
            'sample_id' => $sample['id'],
            'generator' => $sample['generator'],
            'row_count' => count($rows),
            'elapsed_ms' => $elapsed,
        ]);

        return [
            'message' => $sample['name'] . ' generated.',
            'sample_id' => $sample['id'],
            'generator' => $sample['generator'],
            'filename' => basename($path),
            'size_bytes' => filesize($path),
            'elapsed_ms' => $elapsed,
            'download_url' => route('reports.download', ['filename' => basename($path)]),
        ];
    }

    protected function pdf(array $sample, array $rows, string $filename, array $options = []): string
    {
        $engine = match ($sample['generator']) {
            'dompdf' => $this->dompdf,
            'snappy' => $this->snappy,
            'puppeteer' => $this->puppeteer,
        };

        $data = ($sample['meta'] ?? []) + [
            'title' => $sample['name'],
            'rows' => $rows,
            'generated_at' => now()->toDayDateTimeString(),
            'kpis' => SampleData::kpis(),
        ];

        return $engine->generate($sample['view'], $data, $filename, [
            'paper' => strtolower($options['paper'] ?? $sample['paper'] ?? 'a4'),
            'orientation' => $options['orientation'] ?? $sample['orientation'] ?? 'portrait',
        ]);
    }

    protected function xlsx(array $sample, string $filename): string
    {
        $sheets = array_map(function ($sheet) {
            [$headings, $rows] = SampleData::table($sheet['dataset']);

            return [
                'title' => $sheet['title'],
                'headings' => $headings,
                'rows' => array_map('array_values', $rows),
                'totals' => $sheet['totals'] ?? [],
            ];
        }, $sample['sheets']);

        return $this->excel->write($sheets, $filename);
    }

    protected function word(array $sample, array $headings, array $rows, string $filename): string
    {
        if ($sample['id'] === 'docx-memo') {
            $spec = [
                'title' => 'MEMORANDUM',
                'subtitle' => 'Report Integrations · Internal Communication',
                'meta' => [
                    'To' => 'All Department Heads',
                    'From' => 'Office of Operations',
                    'Date' => now()->format('F j, Y'),
                    'Subject' => 'Rollout of the new report generation platform',
                ],
                'paragraphs' => [
                    'Effective next month, all periodic reports will be produced through the Report Integrations platform. '
                    . 'The platform generates PDF, Excel, CSV and Word documents from a single dashboard, using the most suitable rendering engine for each document.',
                    'Please review the attached sample outputs and coordinate with the IT team regarding the report templates your department requires. '
                    . 'Existing manual spreadsheets should be retired once the matching template has been approved.',
                    'Thank you for your cooperation.',
                ],
                'signatory' => ['name' => 'Authorized Signatory', 'role' => 'Operations Manager'],
            ];
        } else {
            $spec = [
                'title' => 'Monthly Service Report',
                'subtitle' => 'September 2026 · Sales Overview',
                'meta' => ['Prepared' => now()->format('F j, Y'), 'Scope' => 'All branches'],
                'paragraphs' => [
                    'This report summarises sales performance across all branches for the month. Figures are net of discounts and are shown in Philippine pesos.',
                    'The table below breaks the results down by branch and product category. Totals are computed from the underlying transaction log.',
                ],
                'table' => ['headings' => $headings, 'rows' => $rows],
            ];
        }

        return $this->docx->write($spec, $filename);
    }
}
