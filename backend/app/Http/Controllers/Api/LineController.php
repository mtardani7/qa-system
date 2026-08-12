<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\LineRequest;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Resources\LineResource;
use App\Models\Line;
use App\Services\LineService;
class LineController extends BaseApiController
{
    public function __construct(private readonly LineService $service) {}
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Line::class); return $this->respondWithResource(LineResource::collection($this->service->paginate($request->validated())), 'Lines retrieved successfully.'); }
    public function store(LineRequest $request) { return $this->respondSuccess(new LineResource($this->service->create($request->validated())), 'Line created successfully.', 201); }
    public function show(Line $line) { $this->authorize('view', $line); return $this->respondSuccess(new LineResource($line->load('plant')), 'Line retrieved successfully.'); }
    public function update(LineRequest $request, Line $line) { return $this->respondSuccess(new LineResource($this->service->update($line, $request->validated())), 'Line updated successfully.'); }
    public function destroy(Line $line) { $this->authorize('delete', $line); $this->service->delete($line); return $this->respondSuccess(null, 'Line deleted successfully.'); }
}
