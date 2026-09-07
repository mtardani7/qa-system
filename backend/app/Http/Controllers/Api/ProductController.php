<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductImportRequest;
use App\Http\Resources\ProductResource;
use App\Exports\ProductImportTemplateExport;
use App\Imports\DailyReportProductMasterImport;
use App\Models\Product;
use App\Services\ProductService;
use App\Support\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Throwable;
class ProductController extends BaseApiController
{
    public function __construct(private readonly ProductService $service) {}
    public function import(ProductImportRequest $request)
    {
        try {
            Excel::import(new DailyReportProductMasterImport(), $request->file('file'));
        } catch (Throwable $exception) {
            report($exception);
            $message = $exception instanceof QueryException && str_contains($exception->getMessage(), 'products_')
                ? 'Import gagal karena ada data Product yang duplikat. Periksa MM, nama, atau kode pada file.'
                : 'Import gagal diproses. Gunakan template Product terbaru dan periksa setiap baris datanya.';
            return ApiResponse::error($message, ['file' => [$message]], 422);
        }

        return $this->respondSuccess(null, 'Products imported successfully.');
    }
    public function template(Request $request) { $this->authorize('create', Product::class); return Excel::download(new ProductImportTemplateExport(), 'product-import-template.xlsx'); }
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Product::class); return $this->respondWithResource(ProductResource::collection($this->service->paginate($request->validated())), 'Products retrieved successfully.'); }
    public function store(ProductRequest $request) { return $this->respondSuccess(new ProductResource($this->service->create($request->validated())), 'Product created successfully.', 201); }
    public function show(Product $product) { $this->authorize('view', $product); return $this->respondSuccess(new ProductResource($product), 'Product retrieved successfully.'); }
    public function update(ProductRequest $request, Product $product) { return $this->respondSuccess(new ProductResource($this->service->update($product, $request->validated())), 'Product updated successfully.'); }
    public function destroy(Product $product) { $this->authorize('delete', $product); $this->service->delete($product); return $this->respondSuccess(null, 'Product deleted successfully.'); }
}
