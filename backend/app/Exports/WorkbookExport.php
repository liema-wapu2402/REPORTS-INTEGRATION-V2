<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/** A workbook made of several SheetExport sheets. */
class WorkbookExport implements Export, WithMultipleSheets
{
    /** @param  array<int,SheetExport>  $sheets */
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return $this->sheets;
    }
}
