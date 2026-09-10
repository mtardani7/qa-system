<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class DailyReportDefectCategoryExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(private readonly DailyReportExportData $data, private readonly string $type) {}
    public function title(): string { return 'Defect '.$this->type; }
    public function array(): array
    {
        $defects = $this->data->defectRows($this->type);
        $total = max(1, array_sum(array_column($defects, 'quantity')));
        $running = 0;
        $rows = [['DEFECT '.$this->type], ['Defect khusus tipe '.$this->type], [], ['Defect', 'Quantity (PCS)', 'Jumlah Kejadian', 'Percentage', 'Cumulative']];
        foreach ($defects as $defect) {
            $running += $defect['quantity'];
            $rows[] = [$defect['name'], $defect['quantity'], $defect['occurrences'], round($defect['quantity'] / $total * 100, 2).'%', round($running / $total * 100, 2).'%'];
        }
        return $rows;
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $sheet->mergeCells('A1:E1'); $sheet->mergeCells('A2:E2');
            $sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:E1')->getFill()->setFillType('solid')->getStartColor()->setARGB('0F766E');
            $sheet->getStyle('A2:E2')->getFont()->setItalic(true)->getColor()->setARGB('64748B');
            $sheet->getStyle('A4:E4')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A4:E4')->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
            $sheet->setAutoFilter('A4:E'.max(4, $sheet->getHighestRow()));
            $sheet->freezePane('A5');
        }];
    }
}