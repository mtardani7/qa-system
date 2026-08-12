<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;

abstract class MasterRepository extends BaseRepository
{
    protected array $searchableColumns = ['code', 'name'];

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        $operator = $this->model->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $search) use ($operator): void {
                $query->where(function (Builder $inner) use ($search, $operator): void {
                    foreach ($this->searchableColumns as $index => $column) {
                        $method = $index === 0 ? 'where' : 'orWhere';
                        $inner->{$method}($column, $operator, "%{$search}%");
                    }
                });
            })
            ->when(array_key_exists('is_active', $filters), fn (Builder $query) => $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)));
    }
}
