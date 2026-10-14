<?php

namespace App\Services\Reports\Samples;

/**
 * The catalog of ready-made sample reports, grouped by generator
 * (engine). Add an entry here and it automatically shows up in the
 * Sample Gallery, the API (/api/samples) and the generate endpoint.
 *
 * Keys:
 *  - generator : dompdf | snappy | puppeteer | excel | csv | docx
 *  - format    : pdf | excel | csv | docx   (matches the `reports.type` column)
 *  - dataset   : a SampleData::table() dataset name
 *  - view      : Blade view (PDF generators only)
 */
final class SampleCatalog
{
    public const GENERATORS = [
        'dompdf' => [
            'label' => 'DomPDF',
            'format' => 'pdf',
            'blurb' => 'Pure PHP renderer. Zero binaries, fastest to set up, great for tabular documents.',
        ],
        'snappy' => [
            'label' => 'Snappy (wkhtmltopdf)',
            'format' => 'pdf',
            'blurb' => 'WebKit based renderer. Needs wkhtmltopdf; table based layouts work best.',
        ],
        'puppeteer' => [
            'label' => 'Puppeteer (Chromium)',
            'format' => 'pdf',
            'blurb' => 'Headless Chrome through Browsershot. Full modern CSS, SVG and gradients.',
        ],
        'excel' => [
            'label' => 'Excel (Maatwebsite)',
            'format' => 'excel',
            'blurb' => 'Styled .xlsx workbooks with multiple sheets, frozen headers and totals.',
        ],
        'csv' => [
            'label' => 'CSV Writer',
            'format' => 'csv',
            'blurb' => 'Lightweight UTF-8 CSV (Excel friendly BOM) for very large row counts.',
        ],
        'docx' => [
            'label' => 'Word (PhpWord)',
            'format' => 'docx',
            'blurb' => 'Editable .docx memos and service reports with tables.',
        ],
    ];

    public static function all(): array
    {
        return [
            // ───────── DomPDF ─────────
            [
                'id' => 'dompdf-consolidated-sales',
                'generator' => 'dompdf', 'format' => 'pdf',
                'name' => 'Consolidated Sales Report',
                'description' => 'Sales per branch and category with discounts and a grand total.',
                'view' => 'reports.sales.consolidated', 'dataset' => 'sales_summary',
                'paper' => 'legal', 'orientation' => 'landscape',
                'tags' => ['sales', 'table', 'totals'],
            ],
            [
                'id' => 'dompdf-employee-directory',
                'generator' => 'dompdf', 'format' => 'pdf',
                'name' => 'Employee Directory',
                'description' => 'Company roster grouped with status badges and department summary.',
                'view' => 'reports.samples.dompdf.employee-directory', 'dataset' => 'employees',
                'paper' => 'a4', 'orientation' => 'portrait',
                'tags' => ['hr', 'roster'],
            ],
            [
                'id' => 'dompdf-inventory-stock',
                'generator' => 'dompdf', 'format' => 'pdf',
                'name' => 'Inventory Stock Status',
                'description' => 'Stock levels with reorder alerts and total inventory value.',
                'view' => 'reports.samples.dompdf.inventory-stock', 'dataset' => 'inventory',
                'paper' => 'a4', 'orientation' => 'portrait',
                'tags' => ['inventory', 'alerts'],
            ],

            // ───────── Snappy ─────────
            [
                'id' => 'snappy-invoice',
                'generator' => 'snappy', 'format' => 'pdf',
                'name' => 'Service Invoice',
                'description' => 'Classic invoice with bill-to block, line items, VAT and amount due.',
                'view' => 'reports.samples.snappy.invoice', 'dataset' => 'invoice',
                'paper' => 'a4', 'orientation' => 'portrait',
                'tags' => ['billing', 'invoice'],
            ],
            [
                'id' => 'snappy-payroll-summary',
                'generator' => 'snappy', 'format' => 'pdf',
                'name' => 'Payroll Summary',
                'description' => 'Per-employee gross, deductions and net pay with signatory lines.',
                'view' => 'reports.samples.snappy.payroll-summary', 'dataset' => 'payroll',
                'paper' => 'a4', 'orientation' => 'landscape',
                'tags' => ['payroll', 'finance'],
            ],

            // ───────── Puppeteer ─────────
            [
                'id' => 'puppeteer-esoa',
                'generator' => 'puppeteer', 'format' => 'pdf',
                'name' => 'eSOA Statement of Account',
                'description' => 'Healthcare statement of account with running balance.',
                'view' => 'reports.claims.esoa', 'dataset' => 'soa',
                'paper' => 'a4', 'orientation' => 'portrait',
                'tags' => ['claims', 'statement'],
                'meta' => [
                    'member_name' => 'Juan Dela Cruz', 'member_no' => 'MBR-2026-00481',
                    'billing_period' => 'Aug 1 - Aug 31, 2026', 'status' => 'Partially Paid',
                    'soa_number' => 'SOA-2026-0831',
                ],
            ],
            [
                'id' => 'puppeteer-executive-dashboard',
                'generator' => 'puppeteer', 'format' => 'pdf',
                'name' => 'Executive Dashboard',
                'description' => 'KPI tiles and SVG charts (gradients, flex and grid layout).',
                'view' => 'reports.samples.puppeteer.executive-dashboard', 'dataset' => 'sales_summary',
                'paper' => 'a4', 'orientation' => 'landscape',
                'tags' => ['charts', 'kpi', 'modern-css'],
            ],

            // ───────── Excel ─────────
            [
                'id' => 'excel-sales-workbook',
                'generator' => 'excel', 'format' => 'excel',
                'name' => 'Sales Workbook',
                'description' => 'Two sheets: branch summary with totals and the full transaction log.',
                'dataset' => 'sales_summary', 'tags' => ['sales', 'multi-sheet'],
                'sheets' => [
                    ['title' => 'Branch Summary', 'dataset' => 'sales_summary', 'totals' => ['gross_sales', 'discounts', 'net_sales']],
                    ['title' => 'Transactions', 'dataset' => 'transactions', 'totals' => ['qty', 'amount']],
                ],
            ],
            [
                'id' => 'excel-inventory',
                'generator' => 'excel', 'format' => 'excel',
                'name' => 'Inventory Sheet',
                'description' => 'Inventory with stock value total, frozen header and autosized columns.',
                'dataset' => 'inventory', 'tags' => ['inventory'],
                'sheets' => [['title' => 'Inventory', 'dataset' => 'inventory', 'totals' => ['stock_value']]],
            ],
            [
                'id' => 'excel-attendance',
                'generator' => 'excel', 'format' => 'excel',
                'name' => 'Attendance Sheet',
                'description' => 'Monthly attendance with absences, late minutes and overtime totals.',
                'dataset' => 'attendance', 'tags' => ['hr', 'attendance'],
                'sheets' => [['title' => 'Attendance', 'dataset' => 'attendance', 'totals' => ['days_present', 'days_absent', 'late_minutes', 'overtime_hours']]],
            ],

            // ───────── CSV ─────────
            [
                'id' => 'csv-transactions',
                'generator' => 'csv', 'format' => 'csv',
                'name' => 'Transactions Export',
                'description' => 'Raw point-of-sale transaction log.',
                'dataset' => 'transactions', 'tags' => ['sales', 'raw'],
            ],
            [
                'id' => 'csv-employees',
                'generator' => 'csv', 'format' => 'csv',
                'name' => 'Employee List Export',
                'description' => 'Employee roster for import into other systems.',
                'dataset' => 'employees', 'tags' => ['hr', 'raw'],
            ],

            // ───────── DOCX ─────────
            [
                'id' => 'docx-memo',
                'generator' => 'docx', 'format' => 'docx',
                'name' => 'Internal Memorandum',
                'description' => 'Formal memo with header block, body paragraphs and a signatory.',
                'dataset' => 'employees', 'tags' => ['memo', 'letter'],
            ],
            [
                'id' => 'docx-service-report',
                'generator' => 'docx', 'format' => 'docx',
                'name' => 'Monthly Service Report',
                'description' => 'Narrative summary plus a styled data table.',
                'dataset' => 'sales_summary', 'tags' => ['report', 'table'],
            ],
        ];
    }

    public static function find(string $id): ?array
    {
        foreach (self::all() as $sample) {
            if ($sample['id'] === $id) {
                return $sample;
            }
        }

        return null;
    }

    /** Catalog payload for the API: grouped by generator, internals stripped. */
    public static function grouped(): array
    {
        $out = [];
        foreach (self::GENERATORS as $key => $info) {
            $out[] = $info + [
                'key' => $key,
                'samples' => array_values(array_map(
                    fn ($s) => self::publicShape($s),
                    array_filter(self::all(), fn ($s) => $s['generator'] === $key)
                )),
            ];
        }

        return $out;
    }

    public static function publicShape(array $s): array
    {
        return [
            'id' => $s['id'],
            'generator' => $s['generator'],
            'format' => $s['format'],
            'name' => $s['name'],
            'description' => $s['description'],
            'paper' => $s['paper'] ?? null,
            'orientation' => $s['orientation'] ?? null,
            'tags' => $s['tags'] ?? [],
        ];
    }
}
