<?php

namespace App\Services\Reports\Pdf\features;

use Illuminate\Http\UploadedFile;

class UploadablePdfService
{
    /**
     * Attaches a user-submitted file to a generated PDF (e.g. merges it
     * in as extra pages, or embeds it as an attachment).
     */
    public function attach(string $pdfPath, UploadedFile $upload): string
    {
        // TODO: actual merge/embed logic.

        return $pdfPath;
    }
}