<?php

namespace App\Services;

use App\DTO\DashboardFilters;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\Cache;

final class DashboardService
{
    public function __construct(private readonly DashboardRepositoryInterface $dashboard) {}

    public function overview(DashboardFilters $filters): array
    {
        return Cache::remember('qms:dashboard:v3:'.sha1(json_encode($filters->key())), now()->addSeconds(60), function () use ($filters): array {
        $summary = $this->dashboard->summary($filters); $production = $summary['production_pcs']; $defects = $summary['defect_qty']; $topDefects = $this->dashboard->topDefects($filters);
        $totalTopDefects = array_sum(array_column($topDefects, 'quantity'));
        $running = 0;
        $pareto = array_map(function (array $defect) use (&$running, $totalTopDefects): array {
            $running += $defect['quantity'];
            $defect['cumulative_percentage'] = $totalTopDefects > 0 ? round(($running / $totalTopDefects) * 100, 2) : 0;
            return $defect;
        }, $topDefects);
        $todayFilters = new DashboardFilters(now()->toImmutable(), $filters->plantId, $filters->shiftId, $filters->machineId, $filters->lineId, $filters->productId, $filters->checkerId, now()->toImmutable(), now()->toImmutable(), 'daily', $filters->scopePlantIds);
        $monthlyDate = $filters->date;
        $monthlyFilters = new DashboardFilters($monthlyDate, $filters->plantId, $filters->shiftId, $filters->machineId, $filters->lineId, $filters->productId, $filters->checkerId, $monthlyDate->startOfMonth(), $monthlyDate->endOfMonth(), 'monthly', $filters->scopePlantIds);
        $today = $this->dashboard->periodSummary($todayFilters); $monthly = $this->dashboard->periodSummary($monthlyFilters);
        return [
            'date' => $filters->date->toDateString(),
            'summary' => ['production_pcs' => $production, 'defect_qty' => $defects, 'defect_rate' => $production > 0 ? round(($defects / $production) * 100, 2) : 0, 'yield' => $production > 0 ? round((($production - $defects) / $production) * 100, 3) : 100],
            'today' => $this->kpis($today), 'monthly' => $this->kpis($monthly),
            'kpi' => $this->kpi($summary),
            'top_defects' => $topDefects,
            'pareto' => $pareto,
            'top_machines' => $this->dashboard->topMachines($filters),
            'top_products' => $this->dashboard->topProducts($filters),
            'monthly_trend' => $this->dashboard->monthlyTrend($filters),
            'production_trend' => $this->dashboard->trend($filters, $filters->period === 'yearly' ? 'month' : ($filters->period === 'monthly' ? 'day' : 'day')),
            'defect_trend' => $this->dashboard->trend($filters, $filters->period === 'yearly' ? 'month' : 'day'),
            'yield_trend' => $this->dashboard->trend($filters, $filters->period === 'yearly' ? 'month' : 'day'),
            'comparisons' => $this->dashboard->comparisons($filters),
        ];
        });
    }

    private function kpis(array $summary): array { $production = (int) $summary['production_pcs']; $defects = (int) $summary['defect_qty']; return ['production' => $production, 'output_pcs' => $production, 'defects' => $defects, 'defect_rate' => $production > 0 ? round(($defects / $production) * 100, 2) : 0, 'yield' => $production > 0 ? round((($production - $defects) / $production) * 100, 3) : 100]; }
    private function kpi(array $summary): array { $actual = $this->kpis($summary); $targetYield = (float) env('QMS_TARGET_YIELD', 98); $targetDefectRate = (float) env('QMS_TARGET_DEFECT_RATE', 2); return ['target_yield' => $targetYield, 'actual_yield' => $actual['yield'], 'yield_indicator' => $actual['yield'] >= $targetYield ? 'green' : ($actual['yield'] >= $targetYield - 2 ? 'yellow' : 'red'), 'target_defect_rate' => $targetDefectRate, 'actual_defect_rate' => $actual['defect_rate'], 'defect_rate_indicator' => $actual['defect_rate'] <= $targetDefectRate ? 'green' : ($actual['defect_rate'] <= $targetDefectRate + 1 ? 'yellow' : 'red')]; }
}
