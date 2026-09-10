<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

/**
 * Resume sheet inspired by the W36 weekly template: per-section MC/change-over
 * blocks (col B/D/E), a section ranking table (col L/M), and a defect Pareto
 * table (col B-F), each with a matching chart.
 */
class DailyReportResumeExport implements FromArray, ShouldAutoSize, WithCharts, WithEvents, WithTitle
{
    public function __construct(private readonly DailyReportExportData $data, private readonly string $type) {}

    public function title(): string { return 'Resume '.$this->type; }

    /**
     * Computes every row/column anchor once so array() and charts() always agree.
     */
    private function layout(): array
    {
        $sectionGroups = collect($this->data->sectionRows($this->type))->groupBy('section');
        $sectionTotals = $this->data->sectionTotals($this->type);
        $defects = $this->data->defectRows($this->type);

        $blocks = [];
        $row = 3;
        foreach ($sectionGroups as $section => $items) {
            $items = $items->values();
            $labelRow = $row;
            $headerRow = $row + 1;
            $dataStart = $headerRow + 1;
            $dataEnd = $dataStart + $items->count() - 1;
            $totalRow = $dataEnd + 1;
            $blocks[] = ['section' => $section, 'items' => $items->all(), 'labelRow' => $labelRow, 'headerRow' => $headerRow, 'dataStart' => $dataStart, 'dataEnd' => $dataEnd, 'totalRow' => $totalRow];
            $row = $totalRow + 2;
        }

        $rankHeaderRow = 3;
        $rankDataStart = $rankHeaderRow + 1;
        $rankDataEnd = $rankDataStart + count($sectionTotals) - 1;
        $rankTotalRow = $rankDataEnd + 1;

        $defectHeaderRow = max($row, $rankTotalRow + 2);
        $defectDataStart = $defectHeaderRow + 1;
        $defectDataEnd = $defectDataStart + count($defects) - 1;

        return compact('blocks', 'sectionTotals', 'defects', 'rankHeaderRow', 'rankDataStart', 'rankDataEnd', 'rankTotalRow', 'defectHeaderRow', 'defectDataStart', 'defectDataEnd');
    }

    public function array(): array
    {
        $summary = $this->data->summary($this->type);
        $layout = $this->layout();
        $cells = [];
        $set = function (int $row, int $col, $value) use (&$cells): void {
            $cells[$row][$col] = $value;
        };

        $set(1, 0, 'WEEKLY DAILY QA REPORT - '.$this->type);
        $set(2, 0, 'Total Report'); $set(2, 1, $summary['reports']);
        $set(2, 2, 'Output PCS'); $set(2, 3, $summary['output_pcs']);
        $set(2, 4, 'Output Box'); $set(2, 5, $summary['output_box']);
        $set(2, 6, 'Defect PCS'); $set(2, 7, $summary['defect_qty']);
        $set(2, 8, 'Defect Rate'); $set(2, 9, $summary['defect_rate'].'%');
        $set(2, 10, 'Yield'); $set(2, 11, $summary['yield'].'%');

        $set($layout['rankHeaderRow'], 11, 'SECTION');
        $set($layout['rankHeaderRow'], 12, 'CHANGE OVER');
        foreach ($layout['sectionTotals'] as $index => $item) {
            $r = $layout['rankDataStart'] + $index;
            $set($r, 11, $item['section']);
            $set($r, 12, $item['change_over']);
        }
        if ($layout['sectionTotals'] !== []) {
            $set($layout['rankTotalRow'], 12, array_sum(array_column($layout['sectionTotals'], 'change_over')));
        }

        foreach ($layout['blocks'] as $block) {
            $set($block['labelRow'], 1, strtoupper($block['section']));
            $set($block['headerRow'], 3, 'MC');
            $set($block['headerRow'], 4, 'CO '.strtoupper($block['section']));
            foreach ($block['items'] as $offset => $item) {
                $r = $block['dataStart'] + $offset;
                $set($r, 3, $item['machine']);
                $set($r, 4, $item['change_over']);
            }
            $set($block['totalRow'], 4, array_sum(array_column($block['items'], 'change_over')));
        }

        $set($layout['defectHeaderRow'], 1, 'DEFECT');
        $set($layout['defectHeaderRow'], 2, 'CRITERIA');
        $set($layout['defectHeaderRow'], 3, 'QTY (PCS)');
        $set($layout['defectHeaderRow'], 4, '%');
        $set($layout['defectHeaderRow'], 5, 'CUMULATIVE');
        $total = max(1, array_sum(array_column($layout['defects'], 'quantity')));
        $running = 0;
        foreach ($layout['defects'] as $offset => $defect) {
            $running += $defect['quantity'];
            $r = $layout['defectDataStart'] + $offset;
            $set($r, 1, $defect['name']);
            $set($r, 2, $defect['category']);
            $set($r, 3, $defect['quantity']);
            $set($r, 4, round($defect['quantity'] / $total * 100, 2).'%');
            $set($r, 5, round($running / $total * 100, 2).'%');
        }
        $set($layout['defectDataEnd'] + 2, 1, 'Detail lengkap tersedia di sheet Daily Report Detail.');

        $lastRow = max(array_keys($cells));
        $lastCol = max(array_map(fn ($row) => max(array_keys($row)), $cells));
        $rows = [];
        for ($r = 1; $r <= $lastRow; $r++) {
            $row = [];
            for ($c = 0; $c <= $lastCol; $c++) {
                $row[] = $cells[$r][$c] ?? null;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function charts(): array
    {
        $layout = $this->layout();
        $sheet = "'Resume {$this->type}'";
        $charts = [];

        foreach ($layout['blocks'] as $index => $block) {
            if ($block['items'] === []) continue;
            $count = count($block['items']);
            $categories = new DataSeriesValues('String', "{$sheet}!\$D\${$block['dataStart']}:\$D\${$block['dataEnd']}", null, $count);
            $values = new DataSeriesValues('Number', "{$sheet}!\$E\${$block['dataStart']}:\$E\${$block['dataEnd']}", null, $count);
            $series = new DataSeries(DataSeries::TYPE_PIECHART, null, [0], [], [$categories], [$values]);
            $chart = new Chart(
                'section_'.$this->type.'_'.$index,
                new Title('CO '.strtoupper($block['section'])),
                new Legend(Legend::POSITION_RIGHT),
                new PlotArea(new Layout(), [$series]),
            );
            $chart->setTopLeftPosition('G'.$block['labelRow']);
            $chart->setBottomRightPosition('L'.($block['totalRow'] + 1));
            $charts[] = $chart;
        }

        if ($layout['sectionTotals'] !== []) {
            $count = count($layout['sectionTotals']);
            $categories = new DataSeriesValues('String', "{$sheet}!\$L\${$layout['rankDataStart']}:\$L\${$layout['rankDataEnd']}", null, $count);
            $values = new DataSeriesValues('Number', "{$sheet}!\$M\${$layout['rankDataStart']}:\$M\${$layout['rankDataEnd']}", null, $count);
            $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, [0], [], [$categories], [$values]);
            $series->setPlotDirection(DataSeries::DIRECTION_COL);
            $chart = new Chart(
                'change_over_'.$this->type,
                new Title('CHANGE OVER '.$this->type),
                new Legend(Legend::POSITION_RIGHT),
                new PlotArea(new Layout(), [$series]),
            );
            $chart->setTopLeftPosition('O3');
            $chart->setBottomRightPosition('U'.($layout['rankTotalRow'] + 12));
            $charts[] = $chart;
        }

        if ($layout['defects'] !== []) {
            $count = count($layout['defects']);
            $label = new DataSeriesValues('String', "{$sheet}!\$B\${$layout['defectHeaderRow']}", null, 1);
            $categories = new DataSeriesValues('String', "{$sheet}!\$B\${$layout['defectDataStart']}:\$B\${$layout['defectDataEnd']}", null, $count);
            $values = new DataSeriesValues('Number', "{$sheet}!\$D\${$layout['defectDataStart']}:\$D\${$layout['defectDataEnd']}", null, $count);
            $series = new DataSeries(DataSeries::TYPE_BARCHART, DataSeries::GROUPING_CLUSTERED, [0], [$label], [$categories], [$values]);
            $series->setPlotDirection(DataSeries::DIRECTION_COL);
            $chart = new Chart(
                'defect_'.$this->type,
                new Title('TEMUAN SAMPLING '.$this->type),
                new Legend(Legend::POSITION_RIGHT),
                new PlotArea(new Layout(), [$series]),
            );
            $chart->setTopLeftPosition('H'.$layout['defectHeaderRow']);
            $chart->setBottomRightPosition('P'.($layout['defectHeaderRow'] + 16));
            $charts[] = $chart;
        }

        return $charts;
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event): void {
            $sheet = $event->sheet->getDelegate();
            $layout = $this->layout();

            $sheet->mergeCells('A1:M1');
            $sheet->getStyle('A1:M1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:M1')->getFill()->setFillType('solid')->getStartColor()->setARGB('0F766E');

            $sheet->getStyle('A2:L2')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A2:L2')->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');

            $sheet->getStyle('L'.$layout['rankHeaderRow'].':M'.$layout['rankHeaderRow'])->getFont()->setBold(true);
            $sheet->getStyle('L'.$layout['rankHeaderRow'].':M'.$layout['rankHeaderRow'])->getFill()->setFillType('solid')->getStartColor()->setARGB('E2E8F0');

            foreach ($layout['blocks'] as $block) {
                $sheet->getStyle('B'.$block['labelRow'])->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('D'.$block['headerRow'].':E'.$block['headerRow'])->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
                $sheet->getStyle('D'.$block['headerRow'].':E'.$block['headerRow'])->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
                $sheet->getStyle('E'.$block['totalRow'])->getFont()->setBold(true);
            }

            $defectHeader = $layout['defectHeaderRow'];
            $sheet->getStyle('B'.$defectHeader.':F'.$defectHeader)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('B'.$defectHeader.':F'.$defectHeader)->getFill()->setFillType('solid')->getStartColor()->setARGB('155E75');
            if ($layout['defectDataEnd'] >= $layout['defectDataStart']) {
                $sheet->setAutoFilter('B'.$defectHeader.':F'.$layout['defectDataEnd']);
            }
            $sheet->freezePane('B'.($defectHeader + 1));

            foreach (['A' => 3, 'B' => 30, 'C' => 14, 'D' => 16, 'E' => 14, 'F' => 12, 'G' => 3, 'H' => 12, 'I' => 12, 'J' => 12, 'K' => 12, 'L' => 16, 'M' => 14] as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }
        }];
    }
}