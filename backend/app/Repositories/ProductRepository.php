<?php
namespace App\Repositories;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
final class ProductRepository extends MasterRepository implements ProductRepositoryInterface
{
    protected array $searchableColumns = ['mm_number', 'code', 'name', 'category'];
    public function __construct(Product $model) { parent::__construct($model); }
    public function query(): Builder { return parent::query()->with('plant'); }
    protected function applyFilters(Builder $query, array $filters): Builder { return parent::applyFilters($query, $filters)->when($filters['plant_id'] ?? null, fn ($q, $value) => $q->where('plant_id', $value)); }
    protected function allowedSorts(): array { return ['mm_number', 'qty_per_box', 'category', 'created_at']; }
}
