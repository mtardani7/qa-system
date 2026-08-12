<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @implements BaseRepositoryInterface<TModel>
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    public function __construct(protected readonly Model $model) {}

    public function findOrFail(int|string $id): Model { return $this->query()->findOrFail($id); }

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = min(max((int) ($filters['per_page'] ?? 20), 1), 100);
        $sort = $this->resolveSort($filters['sort'] ?? null);
        $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $this->applyFilters($this->query(), $filters)
            ->orderBy($sort, $direction)
            ->paginate($perPage);
    }

    public function create(array $attributes): Model { return $this->model->newQuery()->create($attributes); }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();
        return $model->refresh();
    }

    public function delete(Model $model): void { $model->delete(); }

    public function query(): Builder { return $this->model->newQuery(); }

    protected function applyFilters(Builder $query, array $filters): Builder
    {
        return $query;
    }

    protected function resolveSort(?string $sort): string
    {
        return in_array($sort, $this->allowedSorts(), true) ? $sort : $this->model->getKeyName();
    }

    protected function allowedSorts(): array
    {
        return [$this->model->getKeyName()];
    }
}
