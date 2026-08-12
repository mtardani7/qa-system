<?php
namespace App\Services;
use App\Repositories\Contracts\ProductRepositoryInterface;
final class ProductService extends BaseService { public function __construct(ProductRepositoryInterface $repository) { parent::__construct($repository); } }
