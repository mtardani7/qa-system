<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @template TModel of Model
 * @template TRepository of BaseRepositoryInterface<TModel>
 */
abstract class BaseService
{
    /** @param TRepository $repository */
    public function __construct(protected readonly BaseRepositoryInterface $repository) {}

    /** @return LengthAwarePaginator<int, TModel> */
    public function paginate(array $filters = []): LengthAwarePaginator { return $this->repository->paginate($filters); }

    /** @return TModel */
    public function create(array $attributes): Model { return DB::transaction(fn (): Model => $this->repository->create($attributes)); }

    /** @return TModel */
    public function update(Model $model, array $attributes): Model { return DB::transaction(fn (): Model => $this->repository->update($model, $attributes)); }

    public function delete(Model $model): void
    {
        DB::transaction(function () use ($model): void {
            $this->repository->delete($model);
        });
    }
}
