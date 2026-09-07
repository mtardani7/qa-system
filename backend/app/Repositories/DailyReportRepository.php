<?php

namespace App\Repositories;

use App\Models\DailyReport;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

final class DailyReportRepository extends BaseRepository implements DailyReportRepositoryInterface
{
    public function __construct(DailyReport $model) { parent::__construct($model); }

    /** Columns actually consumed by DailyReportResource; avoids SELECT * on a high-volume table. */
    private const LIST_COLUMNS = [
        'id', 'plant_id', 'machine_id', 'shift_id', 'product_id', 'product_type',
        'checker_id', 'checker_2_id', 'mm_number', 'po_number', 'output_box',
        'qty_per_box', 'output_pcs', 'defect_id', 'quantity_defect', 'finding_range_box',
        'finding_observation', 'category', 'qa_checker_id', 'production_date', 'remarks',
        'result', 'status', 'created_at',
    ];

    public function query(): Builder
    {
        return parent::query()->select(self::LIST_COLUMNS)->with(['plant:id,code,name', 'machine:id,plant_id,line_id,code,name,machine_number', 'shift:id,code,name', 'product:id,code,name,mm_number,description,qty_per_box,category', 'checker:id,employee_number,name,position,plant_id', 'checker2:id,employee_number,name', 'defects.defect:id,code,name,category', 'defect:id,code,name,category']);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $operator = $this->model->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        return $query
            ->when(array_key_exists('scope_plant_ids', $filters), fn ($q) => $q->whereIn('plant_id', $filters['scope_plant_ids']))
            ->when($filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value))
            ->when($filters['machine_id'] ?? null, fn ($q, $value) => $q->where('machine_id', $value))
            ->when($filters['shift_id'] ?? null, fn ($q, $value) => $q->where('shift_id', $value))
            ->when($filters['product_id'] ?? null, fn ($q, $value) => $q->where('product_id', $value))
            ->when($filters['checker_id'] ?? null, fn ($q, $value) => $q->where('checker_id', $value))
            ->when($filters['qa_checker_id'] ?? null, fn ($q, $value) => $q->where('qa_checker_id', $value))
            ->when($filters['product_type'] ?? null, fn ($q, $value) => $q->where('product_type', $value))
            ->when($filters['result'] ?? null, fn ($q, $value) => $q->where('result', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when(filter_var($filters['output_box_zero'] ?? false, FILTER_VALIDATE_BOOLEAN), fn ($q) => $q->where('output_box', 0))
            ->when($filters['defect_id'] ?? null, fn ($q, $value) => $q->whereHas('defects', fn ($defects) => $defects->where('defect_id', $value)))
            ->when($filters['production_date'] ?? null, fn ($q, $value) => $q->whereDate('production_date', $value))
            ->when($filters['production_date_from'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '>=', $value))
            ->when($filters['production_date_to'] ?? null, fn ($q, $value) => $q->whereDate('production_date', '<=', $value))
            ->when($filters['search'] ?? null, function ($q, $search) use ($operator): void {
                $q->where(function ($inner) use ($operator, $search): void {
                    $term = "%{$search}%";
                    $inner->where('mm_number', $operator, $term)
                        ->orWhere('po_number', $operator, $term)
                        ->orWhereHas('product', fn ($products) => $products->where('mm_number', $operator, $term)->orWhere('name', $operator, $term)->orWhere('description', $operator, $term))
                        ->orWhereHas('machine', fn ($machines) => $machines->where('name', $operator, $term)->orWhere('code', $operator, $term))
                        ->orWhereHas('shift', fn ($shifts) => $shifts->where('name', $operator, $term))
                        ->orWhereHas('checker', fn ($checkers) => $checkers->where('name', $operator, $term)->orWhere('employee_number', $operator, $term));
                    $inner->orWhereHas('checker2', fn ($checkers) => $checkers->where('name', $operator, $term)->orWhere('employee_number', $operator, $term));
                });
            });
    }

    protected function allowedSorts(): array { return ['production_date', 'machine_id', 'po_number', 'output_box', 'output_pcs', 'quantity_defect', 'created_at', 'id']; }
}
