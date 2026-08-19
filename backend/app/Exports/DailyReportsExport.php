<?php

namespace App\Exports;

use App\Models\DailyReport;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DailyReportsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    public function __construct(private readonly array $filters) {}

    public function query(): Builder
    {
        return DailyReport::query()->with(['plant', 'machine', 'shift', 'product', 'checker', 'checker2', 'defects.defect'])->when($this->filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value))->when($this->filters['shift_id'] ?? null, fn ($q, $value) => $q->where('shift_id', $value))->when($this->filters['machine_id'] ?? null, fn ($q, $value) => $q->where('machine_id', $value))->when($this->filters['product_type'] ?? null, fn ($q, $value) => $q->where('product_type', $value))->when($this->filters['checker_id'] ?? null, fn ($q, $value) => $q->where('checker_id', $value))->when($this->filters['result'] ?? null, fn ($q, $value) => $q->where('result', $value))->when($this->filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))->when($this->filters['production_date_from'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '>=', $value))->when($this->filters['production_date_to'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '<=', $value))->when($this->filters['search'] ?? null, function ($q, $search): void { $q->where(function ($inner) use ($search): void { $term = '%'.$search.'%'; $inner->where('mm_number', 'ilike', $term)->orWhere('po_number', 'ilike', $term)->orWhereHas('product', fn ($products) => $products->where('name', 'ilike', $term)->orWhere('description', 'ilike', $term)); }); })->orderBy('production_date')->orderBy('id');
    }

    public function headings(): array { return ['Production Date', 'Shift', 'Machine', 'Product Type', 'MM Number', 'Item Name', 'PO Number', 'Qty / Box', 'Output PCS', 'Output Box', 'Findings Range (Box)', 'Findings Quantity (PCS)', 'Defect Description', 'Defect Category', 'Result', 'Checked by QA 1', 'Checked by QA 2', 'Remarks']; }
    public function map($report): array { return [$report->production_date?->format('Y-m-d'), $report->shift?->name, $report->machine?->name, $report->product_type, $report->mm_number, $report->product?->name, $report->po_number, $report->qty_per_box, $report->output_pcs, $report->output_box, $report->finding_range_box, $report->defects->sum('quantity'), $report->defects->map(fn ($detail) => $detail->remarks ?: $detail->defect?->description ?: $detail->defect?->name)->filter()->unique()->implode(', '), $report->defects->map(fn ($detail) => $detail->defect?->category)->filter()->unique()->implode(', '), $report->result, $report->checker?->name, $report->checker2?->name, $report->remarks]; }
    public function chunkSize(): int { return 1000; }
}
