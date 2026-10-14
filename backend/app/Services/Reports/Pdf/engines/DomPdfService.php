<?php

namespace App\Services\Reports\Pdf\engines;

use Barryvdh\DomPDF\Facade\Pdf as DomPdf;

class DomPdfService implements PdfEngineInterface
{
    public function generate(string $view, array $data, string $filename, array $layout): string
    {
        $pdf = DomPdf::loadView($view, $data)
            ->setPaper($layout['paper'] ?? 'a4', $layout['orientation'] ?? 'portrait');

        $directory = storage_path('app/reports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $path = $directory . DIRECTORY_SEPARATOR . $filename;
        $pdf->save($path);

        return $path;
    }
}
