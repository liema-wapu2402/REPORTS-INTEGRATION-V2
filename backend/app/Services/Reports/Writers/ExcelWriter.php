<?php

namespace App\Services\Reports\Writers;

use App\Exports\SheetExport;
use App\Exports\WorkbookExport;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class ExcelWriter
{
    /**
     * @param  array<int,array{title:string,headings:array,rows:array,totals?:array}>  $sheets
     */
    public function write(array $sheets, string $filename): string
    {
        $exports = array_map(
            fn ($s) => new SheetExport($s['title'], $s['headings'], $s['rows'], $s['totals'] ?? []),
            $sheets
        );

        $path = ReportPaths::for($filename);
        file_put_contents($path, Excel::raw(new WorkbookExport($exports), ExcelFormat::XLSX));

        return $path;
    }
}
