<?php

namespace App\Repositories\Contracts;

use App\DTO\DashboardFilters;

interface DashboardRepositoryInterface
{
    public function summary(DashboardFilters $filters): array;
    public function topDefects(DashboardFilters $filters): array;
    public function topMachines(DashboardFilters $filters): array;
    public function topProducts(DashboardFilters $filters): array;
    public function monthlyTrend(DashboardFilters $filters): array;
    public function trend(DashboardFilters $filters, string $unit): array;
    public function comparisons(DashboardFilters $filters): array;
    public function periodSummary(DashboardFilters $filters): array;
}
