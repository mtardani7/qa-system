<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class DailyReportDefectsExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(private readonly DailyReportExportData $data) {}
    public function title(): string { return 'Defect Summary'; }
    public function array(): array
    {
        return [
            ['DEFECT SUMMARY'],
            ['Sorted by total defect quantity'],
            [],
            ['Defect Description', 'Category', 'Occurrences', 'Quantity (PCS)'],
            ...array_map(fn (array $row) => [$row['name'], $row['category'], $row['occurrences'], $row['quantity']], $this->data->defectRows()),
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $sheet->mergeCells('A1:D1'); $sheet->mergeCells('A2:D2');
            $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:D1')->getFill()->setFillType('solid')->getStartColor()->setARGB('0F766E');
            $sheet->getStyle('A2:D2')->getFont()->setItalic(true)->getColor()->setARGB('64748B');
            $sheet->getStyle('A4:D4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A4:D4')->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
            $sheet->setAutoFilter('A4:D'.max(4, $sheet->getHighestRow()));
            $sheet->freezePane('A5');
        }];
    }
}