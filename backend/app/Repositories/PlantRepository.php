<?php

namespace App\Repositories;

use App\Models\Plant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\PlantRepositoryInterface;

final class PlantRepository extends MasterRepository implements PlantRepositoryInterface
{
    public function __construct(Plant $model)
    {
        parent::__construct($model);
    }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $operator = $this->model->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        return $query
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($inner) => $inner->where('code', $operator, "%{$search}%")->orWhere('name', $operator, "%{$search}%")))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)));
    }

    protected function allowedSorts(): array { return ['code', 'name', 'created_at']; }

    public function delete(Model $model): void
    {
        $model->forceDelete();
    }
}
