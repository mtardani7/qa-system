<?php
namespace App\Repositories;
use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
final class ProductRepository extends MasterRepository implements ProductRepositoryInterface
{
    protected array $searchableColumns = ['mm_number', 'code', 'name', 'category'];
    public function __construct(Product $model) { parent::__construct($model); }
    protected function allowedSorts(): array { return ['mm_number', 'qty_per_box', 'category', 'created_at']; }
}
