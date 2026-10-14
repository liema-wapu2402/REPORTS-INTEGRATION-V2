<?php

namespace App\Services\Reports\Pdf\features;

class FillablePdfService
{
    /**
     * Lists AcroForm field names on a template so the frontend can render
     * a matching input form.
     */
    public function listFields(string $templatePath): array
    {
        // TODO: actual AcroForm field extraction (e.g. via pdftk / FPDM).
        return [];
    }

    /**
     * Fills a PDF template's form fields with submitted values.
     *
     * @param  string  $template  Template filename, relative to storage/app/templates.
     * @param  array   $values    ['field_name' => 'value'].
     * @param  bool    $flatten   Lock the result from further editing.
     */
    public function generate(?string $template, array $values, string $filename, bool $flatten = true): string
    {
        $templatePath = storage_path('app/templates/' . $template);
        $path = storage_path('app/reports/' . $filename);

        // TODO: actual field-filling logic (e.g. pdftk fill-form, or FPDI overlay).

        return $path;
    }
}