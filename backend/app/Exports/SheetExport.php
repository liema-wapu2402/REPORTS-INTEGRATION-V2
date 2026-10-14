<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * One styled worksheet: dark header row, frozen header, zebra rows,
 * optional bold TOTAL row summing the named columns.
 */
class SheetExport implements FromArray, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    private array $body;

    /**
     * @param  array<int,array<string,mixed>>  $rows       Associative rows.
     * @param  array<int,string>               $totalCols  Column keys to sum in a TOTAL row.
     */
    public function __construct(
        private string $title,
        private array $headings,
        array $rows,
        private array $totalCols = [],
    ) {
        $this->body = array_map(fn ($r) => array_values((array) $r), $rows);

        if ($this->totalCols && $this->body) {
            $total = array_fill(0, count($headings), '');
            $total[0] = 'TOTAL';
            foreach ($this->totalCols as $col) {
                $idx = array_search($col, $headings, true);
                if ($idx !== false) {
                    $total[$idx] = array_sum(array_column($this->body, $idx));
                }
            }
            $this->body[] = $total;
        }
    }

    public function array(): array
    {
        return $this->body;
    }

    public function headings(): array
    {
        return array_map(fn ($h) => ucwords(str_replace('_', ' ', (string) $h)), $this->headings);
    }

    public function title(): string
    {
        return mb_substr(preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $this->title), 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $cols = count($this->headings);
        $lastCol = Coordinate::stringFromColumnIndex(max($cols, 1));
        $lastRow = count($this->body) + 1;

        $sheet->freezePane('A2');
        $sheet->getRowDimension(1)->setRowHeight(22);

        for ($r = 3; $r <= $lastRow; $r += 2) {
            $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        }

        if ($this->totalCols && $this->body) {
            $sheet->getStyle("A{$lastRow}:{$lastCol}{$lastRow}")->applyFromArray([
                'font' => ['bold' => true],
                'borders' => ['top' => ['borderStyle' => 'medium', 'color' => ['rgb' => '0891B2']]],
            ]);
        }

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
            ],
        ];
    }
}
