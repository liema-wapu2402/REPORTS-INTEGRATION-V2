<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;

class SystemController extends Controller
{
    /** GET /api/system/about - static facts shown on the About page. */
    public function about()
    {
        return response()->json([
            'name' => 'Report Integrations',
            'tagline' => 'One dashboard. Every report format.',
            'version' => config('reports.version'),
            'owner' => 'AVANT GUARD',
            'stack' => [
                'backend' => 'Laravel ' . app()->version() . ' · PHP ' . PHP_VERSION,
                'frontend' => 'React 18 · TypeScript · Vite · Tailwind CSS',
            ],
            'totals' => [
                'reports_generated' => Report::count(),
                'storage_bytes' => (int) Report::sum('size_bytes'),
            ],
        ]);
    }

    /** GET /api/system/health - is each generator actually usable on this machine? */
    public function health()
    {
        $wk = config('reports.wkhtmltopdf_binary');
        $node = config('reports.node_binary');

        $engines = [
            $this->engine('dompdf', 'DomPDF', class_exists(\Barryvdh\DomPDF\Facade\Pdf::class), 'composer require barryvdh/laravel-dompdf'),
            $this->engine('snappy', 'Snappy', class_exists(\Barryvdh\Snappy\Facades\SnappyPdf::class) && is_file($wk), 'Install wkhtmltopdf and set WKHTMLTOPDF_BINARY'),
            $this->engine('puppeteer', 'Puppeteer', class_exists(\Spatie\Browsershot\Browsershot::class) && is_file($node) && is_dir(base_path('node_modules/puppeteer')), 'npm install puppeteer --prefix .'),
            $this->engine('excel', 'Excel', class_exists(\Maatwebsite\Excel\Facades\Excel::class), 'composer require maatwebsite/excel'),
            $this->engine('csv', 'CSV', true, ''),
            $this->engine('docx', 'Word', class_exists(\PhpOffice\PhpWord\PhpWord::class), 'composer require phpoffice/phpword'),
        ];

        $dbOk = true;
        try {
            Report::count();
        } catch (\Throwable) {
            $dbOk = false;
        }

        return response()->json([
            'status' => $dbOk && collect($engines)->contains('ready', true) ? 'ok' : 'degraded',
            'database' => $dbOk,
            'engines' => $engines,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    private function engine(string $key, string $label, bool $ready, string $fix): array
    {
        return ['key' => $key, 'label' => $label, 'ready' => $ready, 'fix' => $ready ? null : $fix];
    }
}
