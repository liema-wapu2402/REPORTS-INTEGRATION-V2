<?php

namespace App\Services\Reports\Pdf\engines;

use Spatie\Browsershot\Browsershot;

class PuppeteerPdfService implements PdfEngineInterface
{
    public function generate(string $view, array $data, string $filename, array $layout): string
    {
        $html = view($view, $data)->render();

        $directory = storage_path('app/reports');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        $path = $directory . DIRECTORY_SEPARATOR . $filename;

        Browsershot::html($html)
            ->setNodeBinary(config('reports.node_binary'))
            ->setNpmBinary(config('reports.npm_binary'))
            ->setChromePath(config('reports.chrome_binary'))
            ->noSandbox()
            ->addChromiumArguments(['disable-dev-shm-usage', 'disable-gpu'])
            ->showBackground()
            ->format(strtoupper($layout['paper'] ?? 'a4'))
            ->landscape(($layout['orientation'] ?? 'portrait') === 'landscape')
            ->save($path);

        return $path;
    }
}
