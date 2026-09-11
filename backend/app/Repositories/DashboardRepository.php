<?php

namespace App\Repositories;

use App\DTO\DashboardFilters;
use App\Models\DailyReport;
use App\Repositories\Contracts\DashboardRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class DashboardRepository implements DashboardRepositoryInterface
{
    public function summary(DashboardFilters $filters): array { return $this->aggregate($this->scopedQuery($filters)); }
    public function periodSummary(DashboardFilters $filters): array { return $this->aggregate($this->scopedQuery($filters)); }

    public function topDefects(DashboardFilters $filters): array
    {
        $child = DB::table('daily_report_defects')->join('daily_reports', 'daily_reports.id', '=', 'daily_report_defects.daily_report_id')->join('defects', 'defects.id', '=', 'daily_report_defects.defect_id')->whereNotNull('daily_report_defects.quantity')->whereBetween('daily_reports.production_date', [$filters->from()->toDateString(), $filters->to()->toDateString()])->whereNotIn('daily_reports.status', ['cancelled'])->when($filters->scopePlantIds !== null, fn ($q) => $q->whereIn('daily_reports.plant_id', $filters->scopePlantIds))->when($filters->plantId, fn ($q, $v) => $q->where('daily_reports.plant_id', $v))->when($filters->lineId, fn ($q, $v) => $q->where('daily_reports.line_id', $v))->when($filters->machineId, fn ($q, $v) => $q->where('daily_reports.machine_id', $v))->when($filters->shiftId, fn ($q, $v) => $q->where('daily_reports.shift_id', $v))->when($filters->productId, fn ($q, $v) => $q->where('daily_reports.product_id', $v))->when($filters->checkerId, fn ($q, $v) => $q->where('daily_reports.checker_id', $v))->selectRaw('MIN(daily_reports.id) AS report_id, defects.id, defects.code, defects.name, defects.category, SUM(daily_report_defects.quantity) AS quantity')->groupBy('defects.id', 'defects.code', 'defects.name', 'defects.category')->orderByRaw('quantity DESC NULLS LAST')->limit(10)->get();
        if ($child->isEmpty()) return $this->legacyDefects($filters);
        return $child->map(fn ($row): array => ['id' => (int) $row->id, 'report_id' => (int) $row->report_id, 'code' => $row->code, 'name' => $row->name, 'category' => $row->category, 'quantity' => (int) $row->quantity])->all();
    }

    public function topMachines(DashboardFilters $filters): array { return $this->ranking($filters, 'machines', 'machine_id', 'output_pcs'); }
    public function topProducts(DashboardFilters $filters): array { return $this->ranking($filters, 'products', 'product_id', 'output_pcs'); }

    public function changeOverBySection(DashboardFilters $filters): array
    {
        return $this->changeOverRanking($filters, 'section', 'CO');
    }

    public function changeOverByMachine(DashboardFilters $filters): array
    {
        return $this->changeOverRanking($filters, 'name', 'MC');
    }

    public function defectCategories(DashboardFilters $filters): array
    {
        $rows = $this->scopedQuery($filters)
            ->join('daily_report_defects', 'daily_report_defects.daily_report_id', '=', 'daily_reports.id')
            ->join('defects', 'defects.id', '=', 'daily_report_defects.defect_id')
            ->selectRaw("COALESCE(NULLIF(UPPER(defects.category), ''), 'OTHER') AS category, SUM(daily_report_defects.quantity) AS value, MIN(daily_reports.id) AS report_id")
            ->groupByRaw("COALESCE(NULLIF(UPPER(defects.category), ''), 'OTHER')")
            ->orderByDesc('value')
            ->get();

        if ($rows->isEmpty()) {
            return $this->scopedQuery($filters)
                ->whereNotNull('daily_reports.category')
                ->selectRaw("COALESCE(NULLIF(UPPER(daily_reports.category), ''), 'OTHER') AS category, SUM(daily_reports.quantity_defect) AS value, MIN(daily_reports.id) AS report_id")
                ->groupByRaw("COALESCE(NULLIF(UPPER(daily_reports.category), ''), 'OTHER')")
                ->orderByDesc('value')
                ->get()
                ->map(fn ($row): array => ['id' => crc32($row->category), 'report_id' => (int) $row->report_id, 'code' => $row->category, 'name' => $row->category, 'value' => (int) $row->value])->all();
        }

        return $rows->map(fn ($row): array => ['id' => crc32($row->category), 'report_id' => (int) $row->report_id, 'code' => $row->category, 'name' => $row->category, 'value' => (int) $row->value])->all();
    }

    public function worstMachineByDefectRate(DashboardFilters $filters): ?array
    {
        $defectExpression = $this->defectExpression();
        $row = $this->scopedQuery($filters)
            ->join('machines', 'machines.id', '=', 'daily_reports.machine_id')
            ->selectRaw("MIN(daily_reports.id) AS report_id, machines.id, machines.code, machines.name, SUM(daily_reports.output_pcs) AS output_pcs, SUM({$defectExpression}) AS defect_qty")
            ->groupBy('machines.id', 'machines.code', 'machines.name')
            ->havingRaw('SUM(daily_reports.output_pcs) > 0')
            ->orderByRaw('(SUM('.$defectExpression.') / SUM(daily_reports.output_pcs)) DESC')
            ->first();

        if (!$row) return null;
        $output = (int) $row->output_pcs;
        $defects = (int) $row->defect_qty;
        return ['id' => (int) $row->id, 'report_id' => (int) $row->report_id, 'code' => $row->code, 'name' => $row->name, 'value' => $output > 0 ? round(($defects / $output) * 100, 2) : 0, 'output_pcs' => $output, 'defect_qty' => $defects];
    }

    public function monthlyTrend(DashboardFilters $filters): array { return $this->trend($filters, 'day'); }

    public function trend(DashboardFilters $filters, string $unit): array
    {
        $postgres = DB::connection()->getDriverName() === 'pgsql';
        $format = match ($unit) { 'week' => 'YYYY-IW', 'month' => 'YYYY-MM', 'year' => 'YYYY', default => 'YYYY-MM-DD' };
        $bucketExpression = $postgres ? "to_char(date_trunc('{$unit}', production_date), '{$format}')" : match ($unit) { 'week' => "strftime('%Y-%W', production_date)", 'month' => "strftime('%Y-%m', production_date)", 'year' => "strftime('%Y', production_date)", default => "strftime('%Y-%m-%d', production_date)" };
        $expression = $this->defectExpression();
        return $this->scopedQuery($filters)->selectRaw("MIN(daily_reports.id) AS report_id, {$bucketExpression} AS bucket, SUM(output_pcs) AS production_pcs, SUM({$expression}) AS defect_qty")->groupBy('bucket')->orderBy('bucket')->get()->map(fn ($row): array => $this->metricRow((string) $row->bucket, (int) $row->production_pcs, (int) $row->defect_qty, (int) $row->report_id))->all();
    }

    public function comparisons(DashboardFilters $filters): array
    {
        return ['shifts' => $this->groupComparison($filters, 'shifts', 'shift_id'), 'machines' => $this->groupComparison($filters, 'machines', 'machine_id'), 'plants' => $this->groupComparison($filters, 'plants', 'plant_id')];
    }

    private function ranking(DashboardFilters $filters, string $table, string $foreignKey, string $metric): array
    {
        $rows = $this->scopedQuery($filters)->join($table, "{$table}.id", '=', "daily_reports.{$foreignKey}")->selectRaw("MIN(daily_reports.id) AS report_id, {$table}.id, {$table}.code, {$table}.name, SUM(daily_reports.{$metric}) AS value")->groupBy("{$table}.id", "{$table}.code", "{$table}.name")->orderByDesc('value')->limit(10)->get();
        return $rows->map(fn ($row): array => ['id' => (int) $row->id, 'report_id' => (int) $row->report_id, 'code' => $row->code, 'name' => $row->name, 'value' => (int) $row->value, 'output_pcs' => (int) $row->value])->all();
    }

    private function changeOverRanking(DashboardFilters $filters, string $groupColumn, string $code): array
    {
        $query = $this->scopedQuery($filters)->join('machines', 'machines.id', '=', 'daily_reports.machine_id');
        $column = $groupColumn === 'section' ? "COALESCE(NULLIF(machines.section, ''), 'UNASSIGNED')" : 'machines.name';
        return $query->selectRaw("MIN(daily_reports.id) AS report_id, {$column} AS name, COUNT(*) AS value")
            ->groupByRaw($column)
            ->orderByDesc('value')
            ->limit(10)
            ->get()
            ->map(fn ($row): array => ['id' => crc32($row->name), 'report_id' => (int) $row->report_id, 'code' => $code, 'name' => $row->name, 'value' => (int) $row->value])->all();
    }

    private function groupComparison(DashboardFilters $filters, string $table, string $foreignKey): array
    {
        return $this->ranking($filters, $table, $foreignKey, 'output_pcs');
    }

    private function legacyDefects(DashboardFilters $filters): array
    {
        return $this->scopedQuery($filters)->whereNotNull('daily_reports.defect_id')->join('defects', 'defects.id', '=', 'daily_reports.defect_id')->selectRaw('MIN(daily_reports.id) AS report_id, defects.id, defects.code, defects.name, defects.category, SUM(daily_reports.quantity_defect) AS quantity')->groupBy('defects.id', 'defects.code', 'defects.name', 'defects.category')->orderByDesc('quantity')->limit(10)->get()->map(fn ($row): array => ['id' => (int) $row->id, 'report_id' => (int) $row->report_id, 'code' => $row->code, 'name' => $row->name, 'category' => $row->category, 'quantity' => (int) $row->quantity])->all();
    }

    private function aggregate(Builder $query): array
    {
        $row = $query->selectRaw('COALESCE(SUM(output_pcs), 0) AS production_pcs, COALESCE(SUM('.$this->defectExpression().'), 0) AS defect_qty')->first();
        return ['production_pcs' => (int) $row->production_pcs, 'defect_qty' => (int) $row->defect_qty];
    }

    private function metricRow(string $bucket, int $production, int $defects, int $reportId): array { return ['date' => $bucket, 'report_id' => $reportId, 'production_pcs' => $production, 'defect_qty' => $defects, 'defect_rate' => $production > 0 ? round(($defects / $production) * 100, 2) : 0, 'yield' => $production > 0 ? round((($production - $defects) / $production) * 100, 3) : 100]; }
    private function defectExpression(): string { return '(CASE WHEN COALESCE(daily_reports.quantity_defect, 0) > 0 THEN daily_reports.quantity_defect ELSE (SELECT COALESCE(SUM(drd.quantity), 0) FROM daily_report_defects drd WHERE drd.daily_report_id = daily_reports.id) END)'; }
    private function scopedQuery(DashboardFilters $filters): Builder { return DailyReport::query()->whereBetween('daily_reports.production_date', [$filters->from()->startOfDay(), $filters->to()->endOfDay()])->whereNotIn('daily_reports.status', ['cancelled'])->when($filters->scopePlantIds !== null, fn (Builder $q) => $q->whereIn('daily_reports.plant_id', $filters->scopePlantIds))->when($filters->plantId, fn (Builder $q, int $v) => $q->where('daily_reports.plant_id', $v))->when($filters->lineId, fn (Builder $q, int $v) => $q->where('daily_reports.line_id', $v))->when($filters->shiftId, fn (Builder $q, int $v) => $q->where('daily_reports.shift_id', $v))->when($filters->machineId, fn (Builder $q, int $v) => $q->where('daily_reports.machine_id', $v))->when($filters->productId, fn (Builder $q, int $v) => $q->where('daily_reports.product_id', $v))->when($filters->checkerId, fn (Builder $q, int $v) => $q->where('daily_reports.checker_id', $v)); }
}
