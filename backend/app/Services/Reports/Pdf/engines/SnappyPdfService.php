<?php

namespace App\Services\Reports\Pdf\engines;

use Barryvdh\Snappy\Facades\SnappyPdf;

class SnappyPdfService implements PdfEngineInterface
{
    public function generate(string $view, array $data, string $filename, array $layout): string
    {
        $directory = storage_path('app/reports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        SnappyPdf::loadView($view, $data)
            ->setOption('orientation', $layout['orientation'] ?? 'portrait')
            ->setOption('page-size', strtoupper($layout['paper'] ?? 'a4'))
            ->save($path);

        return $path;
    }
}