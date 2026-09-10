<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class DailyReportSummaryExport implements FromArray, ShouldAutoSize, WithEvents, WithTitle
{
    public function __construct(private readonly DailyReportExportData $data) {}
    public function title(): string { return 'Summary'; }
    public function array(): array
    {
        $summary = $this->data->summary();
        return [
            ['DAILY QA REPORT'],
            ['One production report per row; defects are grouped by category.'],
            ['Reports', $summary['reports'], 'Output PCS', $summary['output_pcs'], 'Defect PCS', $summary['defect_qty'], 'Defect Rate', $summary['defect_rate'].'%', 'Yield', $summary['yield'].'%'],
            [],
            ['Production Date', 'Shift', 'Machine', 'Plant', 'Product Type', 'MM Number', 'Item Name', 'PO Number', 'Qty / Box', 'Output PCS', 'Output Box', 'Findings Range (Box)', 'Total Defect (PCS)', 'Critical', 'Major', 'Minor', 'Other', 'Result', 'Checked by QA 1', 'Checked by QA 2', 'Remarks'],
            ...$this->data->groupedRows(),
        ];
    }
    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $sheet->mergeCells('A1:U1'); $sheet->mergeCells('A2:U2');
            $sheet->getStyle('A1:U1')->getFont()->setBold(true)->setSize(18)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:U1')->getFill()->setFillType('solid')->getStartColor()->setARGB('0F766E');
            $sheet->getStyle('A2:U2')->getFont()->setItalic(true)->getColor()->setARGB('64748B');
            $sheet->getStyle('A3:J3')->getFont()->setBold(true);
            $sheet->getStyle('A5:U5')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A5:U5')->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
            $sheet->getStyle('A5:U5')->getAlignment()->setWrapText(true);
            $sheet->setAutoFilter('A5:U'.max(5, $sheet->getHighestRow()));
            $sheet->freezePane('A6');
        }];
    }
}
