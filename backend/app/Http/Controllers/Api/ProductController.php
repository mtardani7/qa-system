<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Resources\ProductResource;
use App\Imports\DailyReportProductMasterImport;
use App\Models\Product;
use App\Services\ProductService;
use Maatwebsite\Excel\Facades\Excel;
class ProductController extends BaseApiController
{
    public function __construct(private readonly ProductService $service) {}
    public function import(ProductImportRequest $request) { Excel::import(new DailyReportProductMasterImport(), $request->file('file')); return $this->respondSuccess(null, 'Products imported successfully.'); }
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Product::class); return $this->respondWithResource(ProductResource::collection($this->service->paginate($request->validated())), 'Products retrieved successfully.'); }
    public function store(ProductRequest $request) { return $this->respondSuccess(new ProductResource($this->service->create($request->validated())), 'Product created successfully.', 201); }
    public function show(Product $product) { $this->authorize('view', $product); return $this->respondSuccess(new ProductResource($product), 'Product retrieved successfully.'); }
    public function update(ProductRequest $request, Product $product) { return $this->respondSuccess(new ProductResource($this->service->update($product, $request->validated())), 'Product updated successfully.'); }
    public function destroy(Product $product) { $this->authorize('delete', $product); $this->service->delete($product); return $this->respondSuccess(null, 'Product deleted successfully.'); }
}
