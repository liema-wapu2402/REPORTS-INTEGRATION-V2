<?php

namespace App\Services\Reports\Pdf\engines;
use Spatie\Browsershot\Browsershot;

class PuppeteerPdfService implements PdfEngineInterface
{
    public function generate(string $view, array $data, string $filename, array $layout): string
    {
        $html = view($view, $data)->render();
        $path = storage_path('app/reports/' . $filename);

        Browsershot::html($html)
            ->format(strtoupper($layout['paper'] ?? 'a4'))
            ->landscape(($layout['orientation'] ?? 'portrait') === 'landscape')
            ->save($path);

        return $path;
    }
}
