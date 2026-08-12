<?php

namespace App\Repositories;

use App\Models\QaChecker;
use App\Repositories\Contracts\QaCheckerRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

final class QaCheckerRepository extends MasterRepository implements QaCheckerRepositoryInterface
{
    protected array $searchableColumns = ['employee_number', 'name', 'position'];

    public function __construct(QaChecker $model) { parent::__construct($model); }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return parent::applyFilters($query, $filters)->when($filters['plant_id'] ?? null, fn (Builder $builder, $plantId) => $builder->where('plant_id', $plantId));
    }

    protected function allowedSorts(): array { return ['employee_number', 'name', 'position', 'created_at']; }
}
