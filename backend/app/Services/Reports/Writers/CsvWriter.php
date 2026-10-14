<?php

namespace App\Services\Reports\Writers;

class CsvWriter
{
    /**
     * Streams rows to disk (constant memory) with a UTF-8 BOM so Excel
     * opens accented characters correctly.
     */
    public function write(array $headings, iterable $rows, string $filename): string
    {
        $path = ReportPaths::for($filename);
        $h = fopen($path, 'w');
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, $headings);
        foreach ($rows as $row) {
            fputcsv($h, array_values((array) $row));
        }
        fclose($h);

        return $path;
    }
}
