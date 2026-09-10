<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class DailyReportDetailsExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(private readonly DailyReportExportData $data) {}
    public function title(): string { return 'Daily Report Detail'; }
    public function array(): array
    {
        return [
            ['DAILY QA REPORT - DETAIL'],
            ['One defect per row; report information is repeated for filtering.'],
            [],
            ['Production Date', 'Shift', 'Machine', 'Plant', 'Product Type', 'MM Number', 'Item Name', 'PO Number', 'Qty / Box', 'Output PCS', 'Output Box', 'Findings Range (Box)', 'Result', 'Checked by QA 1', 'Checked by QA 2', 'Remarks', 'Findings Quantity (PCS)', 'Defect', 'Defect Description (Optional)', 'Defect Category'],
            ...$this->data->detailRows(),
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $sheet->mergeCells('A1:T1'); $sheet->mergeCells('A2:T2');
            $sheet->getStyle('A1:T1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:T1')->getFill()->setFillType('solid')->getStartColor()->setARGB('0F766E');
            $sheet->getStyle('A2:T2')->getFont()->setItalic(true)->getColor()->setARGB('64748B');
            $sheet->getStyle('A3:T3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A3:T3')->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
            $sheet->getStyle('A3:T3')->getAlignment()->setWrapText(true);
            $sheet->setAutoFilter('A3:T'.max(3, $sheet->getHighestRow()));
            $sheet->freezePane('A4');
        }];
    }
}
