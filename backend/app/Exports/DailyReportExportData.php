<?php

namespace App\Exports;

use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Collection;

class DailyReportExportData
{
    public readonly Collection $reports;

    public function __construct(array $filters)
    {
        $this->reports = DailyReport::query()
            ->with(['plant', 'line', 'machine', 'shift', 'product', 'checker', 'checker2', 'defects.defect'])
            ->when($filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value))
            ->when($filters['shift_id'] ?? null, fn ($q, $value) => $q->where('shift_id', $value))
            ->when($filters['machine_id'] ?? null, fn ($q, $value) => $q->where('machine_id', $value))
            ->when($filters['product_type'] ?? null, fn ($q, $value) => $q->where('product_type', $value))
            ->when($filters['checker_id'] ?? null, fn ($q, $value) => $q->where('checker_id', $value))
            ->when($filters['result'] ?? null, fn ($q, $value) => $q->where('result', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when(filter_var($filters['output_box_zero'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($q) => $q->where('output_box', 0))
            ->when($filters['production_date_from'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '>=', $value))
            ->when($filters['production_date_to'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '<=', $value))
            ->when($filters['search'] ?? null, function ($q, $search): void {
                $q->where(function ($inner) use ($search): void {
                    $term = '%'.$search.'%';
                    $inner->where('mm_number', 'ilike', $term)
                        ->orWhere('po_number', 'ilike', $term)
                        ->orWhereHas('product', fn ($products) => $products->where('name', 'ilike', $term)->orWhere('description', 'ilike', $term));
                });
            })
            ->orderBy('production_date')
            ->orderBy('id')
            ->get();
    }

    public function reportParts(DailyReport $report): array
    {
        $snapshot = $report->master_snapshot ?? [];
        return [
            'report' => $report,
            'shift' => $snapshot['shift'] ?? $report->shift,
            'machine' => $snapshot['machine'] ?? $report->machine,
            'product' => $snapshot['product'] ?? $report->product,
            'checker' => $snapshot['checker'] ?? $report->checker,
            'checker2' => $snapshot['checker_2'] ?? $report->checker2,
            'defects' => array_key_exists('defects', $snapshot) ? collect($snapshot['defects']) : $report->defects,
        ];
    }

    public function reportsByType(?string $type = null): Collection
    {
        if ($type === null) return $this->reports;
        return $this->reports->filter(fn (DailyReport $report) => strtoupper((string) $report->product_type) === strtoupper($type))->values();
    }

    public function detailRows(): array
    {
        $rows = [];
        foreach ($this->reports as $report) {
            $parts = $this->reportParts($report);
            $base = [
                $report->production_date?->format('Y-m-d'), data_get($parts['shift'], 'name'), data_get($parts['machine'], 'name'), data_get($report->plant, 'code'),
                $report->product_type, $report->mm_number, data_get($parts['product'], 'name'), $report->po_number, $report->qty_per_box, $report->output_pcs,
                $report->output_box, $report->finding_range_box, $report->result, data_get($parts['checker'], 'name'), data_get($parts['checker2'], 'name'), $report->remarks,
            ];
            if ($parts['defects']->isEmpty()) {
                $rows[] = [...$base, null, null, null, null];
                continue;
            }
            foreach ($parts['defects'] as $detail) {
                $rows[] = [
                    ...$base,
                    data_get($detail, 'quantity'),
                    data_get($detail, 'defect.name') ?: data_get($detail, 'defect.description'),
                    data_get($detail, 'remarks'),
                    data_get($detail, 'defect.category'),
                ];
            }
        }
        return $rows;
    }

    public function groupedRows(?string $type = null): array
    {
        $rows = [];
        foreach ($this->reportsByType($type) as $report) {
            $parts = $this->reportParts($report);
            $categories = ['CRITICAL' => [], 'MAJOR' => [], 'MINOR' => [], 'OTHER' => []];
            foreach ($parts['defects'] as $detail) {
                $category = strtoupper((string) (data_get($detail, 'defect.category') ?: 'OTHER'));
                $category = array_key_exists($category, $categories) ? $category : 'OTHER';
                $name = data_get($detail, 'defect.name') ?: data_get($detail, 'defect.description') ?: data_get($detail, 'remarks') ?: 'Unspecified';
                $quantity = (int) data_get($detail, 'quantity', 0);
                $categories[$category][] = $name.($quantity > 0 ? ' ('.$quantity.')' : '');
            }
            $rows[] = [
                $report->production_date?->format('Y-m-d'), data_get($parts['shift'], 'name'), data_get($parts['machine'], 'name'), data_get($report->plant, 'code'),
                $report->product_type, $report->mm_number, data_get($parts['product'], 'name'), $report->po_number, $report->qty_per_box, $report->output_pcs,
                $report->output_box, $report->finding_range_box, $report->quantity_defect, implode('; ', $categories['CRITICAL']), implode('; ', $categories['MAJOR']),
                implode('; ', $categories['MINOR']), implode('; ', $categories['OTHER']), $report->result, data_get($parts['checker'], 'name'), data_get($parts['checker2'], 'name'), $report->remarks,
            ];
        }
        return $rows;
    }

    public function defectRows(?string $type = null): array
    {
        $defects = [];
        foreach ($this->reportsByType($type) as $report) {
            foreach ($this->reportParts($report)['defects'] as $detail) {
                $name = data_get($detail, 'defect.name') ?: data_get($detail, 'defect.description') ?: data_get($detail, 'remarks') ?: 'Unspecified';
                $category = data_get($detail, 'defect.category') ?: '-';
                $key = $category.'|'.$name;
                $defects[$key] ??= ['name' => $name, 'category' => $category, 'occurrences' => 0, 'quantity' => 0];
                $defects[$key]['occurrences']++;
                $defects[$key]['quantity'] += (int) data_get($detail, 'quantity', 0);
            }
        }
        usort($defects, fn ($left, $right) => $right['quantity'] <=> $left['quantity']);
        return array_values($defects);
    }

    public function sectionRows(?string $type = null): array
    {
        $sections = [];
        foreach ($this->reportsByType($type) as $report) {
            $parts = $this->reportParts($report);
            $section = data_get($report->machine, 'section') ?: 'UNASSIGNED';
            $machine = data_get($parts['machine'], 'name') ?: data_get($parts['machine'], 'code') ?: 'UNASSIGNED';
            $key = $section.'|'.$machine;
            $sections[$key] ??= ['section' => $section, 'machine' => $machine, 'change_over' => 0];
            $sections[$key]['change_over']++;
        }
        $rows = array_values($sections);
        usort($rows, fn ($left, $right) => [$left['section'], $left['machine']] <=> [$right['section'], $right['machine']]);
        return $rows;
    }

    public function sectionTotals(?string $type = null): array
    {
        $totals = [];
        foreach ($this->sectionRows($type) as $row) {
            $totals[$row['section']] = ($totals[$row['section']] ?? 0) + $row['change_over'];
        }
        arsort($totals);
        $result = [];
        foreach ($totals as $section => $total) {
            $result[] = ['section' => $section, 'change_over' => $total];
        }
        return $result;
    }

    public function summary(?string $type = null): array
    {
        $reports = $this->reportsByType($type);
        $output = (int) $reports->sum('output_pcs');
        $defects = (int) $reports->sum('quantity_defect');
        return ['reports' => $reports->count(), 'output_pcs' => $output, 'output_box' => $reports->sum('output_box'), 'defect_qty' => $defects, 'defect_rate' => $output > 0 ? round(($defects / $output) * 100, 2) : 0, 'yield' => $output > 0 ? round((($output - $defects) / $output) * 100, 2) : 100];
    }
}
