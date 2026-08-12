<?php
namespace App\Repositories;
use App\Models\Defect;
use App\Repositories\Contracts\DefectRepositoryInterface;
final class DefectRepository extends MasterRepository implements DefectRepositoryInterface
{
    protected array $searchableColumns = ['code', 'name', 'category'];
    public function __construct(Defect $model) { parent::__construct($model); }
    protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query, array $filters): \Illuminate\Database\Eloquent\Builder { return parent::applyFilters($query, $filters)->when($filters['category'] ?? null, fn ($q, $value) => $q->where('category', $value)); }
    protected function allowedSorts(): array { return ['code', 'name', 'category', 'created_at']; }
}
