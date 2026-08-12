<?php

namespace App\Repositories;

use App\Models\DailyReportDefect;
use App\Repositories\Contracts\DailyReportDefectRepositoryInterface;

final class DailyReportDefectRepository extends BaseRepository implements DailyReportDefectRepositoryInterface
{
    public function __construct(DailyReportDefect $model) { parent::__construct($model); }

    public function deleteForReport(int $reportId): void { $this->model->newQuery()->where('daily_report_id', $reportId)->delete(); }

    public function createMany(int $reportId, array $defects): void
    {
        if ($defects === []) return;
        $this->model->newQuery()->insert(array_map(fn (array $defect): array => ['daily_report_id' => $reportId, 'defect_id' => $defect['defect_id'], 'quantity' => $defect['quantity'], 'remarks' => $defect['remarks'] ?? null, 'created_at' => now(), 'updated_at' => now()], $defects));
    }
}
