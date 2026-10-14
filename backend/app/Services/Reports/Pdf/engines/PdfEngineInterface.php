<?php

namespace App\Services\Reports\Pdf\engines;

interface PdfEngineInterface
{
    /**
     * @param  string  $view      Blade view to render.
     * @param  array   $data      Data passed to the view.
     * @param  string  $filename  Output filename.
     * @param  array   $layout    ['paper' => 'a4', 'orientation' => 'portrait'].
     * @return string             Absolute path to the generated PDF.
     */
    public function generate(string $view, array $data, string $filename, array $layout): string;
}