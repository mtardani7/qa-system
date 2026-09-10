<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DailyReportsExport implements WithMultipleSheets
{
    private DailyReportExportData $data;

    public function __construct(array $filters)
    {
        $this->data = new DailyReportExportData($filters);
    }

    public function sheets(): array
    {
        return [
            new DailyReportResumeExport($this->data, 'WIP'),
            new DailyReportDefectCategoryExport($this->data, 'WIP'),
            new DailyReportResumeExport($this->data, 'FG'),
            new DailyReportDefectCategoryExport($this->data, 'FG'),
            new DailyReportDetailsExport($this->data),
        ];
    }
}
