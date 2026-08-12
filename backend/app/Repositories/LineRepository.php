<?php
namespace App\Repositories;
use App\Models\Line;
use App\Repositories\Contracts\LineRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
final class LineRepository extends MasterRepository implements LineRepositoryInterface
{
    protected array $searchableColumns = ['code', 'name'];
    public function __construct(Line $model) { parent::__construct($model); }
    protected function applyFilters(Builder $query, array $filters): Builder { return parent::applyFilters($query, $filters)->when($filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value)); }
    protected function allowedSorts(): array { return ['code', 'name', 'created_at']; }
}
