<?php
namespace App\Repositories;
use App\Models\Machine;
use App\Repositories\Contracts\MachineRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
final class MachineRepository extends MasterRepository implements MachineRepositoryInterface
{
    protected array $searchableColumns = ['code', 'name'];
    public function __construct(Machine $model) { parent::__construct($model); }
    protected function applyFilters(Builder $query, array $filters): Builder { return parent::applyFilters($query, $filters)->when($filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value))->when($filters['line_id'] ?? null, fn ($q, $value) => $q->where('line_id', $value)); }
    protected function allowedSorts(): array { return ['code', 'name', 'machine_number', 'created_at']; }
}
