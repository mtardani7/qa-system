<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface BaseRepositoryInterface
{
    /** @return TModel */
    public function findOrFail(int|string $id): Model;

    /** @return LengthAwarePaginator<int, TModel> */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /** @return TModel */
    public function create(array $attributes): Model;

    /** @return TModel */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): void;

    /** @return Builder<TModel> */
    public function query(): Builder;
}
