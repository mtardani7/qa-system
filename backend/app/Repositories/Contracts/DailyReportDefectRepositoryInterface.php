<?php

namespace App\Repositories\Contracts;

interface DailyReportDefectRepositoryInterface extends BaseRepositoryInterface
{
    public function deleteForReport(int $reportId): void;
    public function createMany(int $reportId, array $defects): void;
}
