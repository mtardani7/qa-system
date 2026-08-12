<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\DefectRequest;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Resources\DefectResource;
use App\Models\Defect;
use App\Services\DefectService;
class DefectController extends BaseApiController
{
    public function __construct(private readonly DefectService $service) {}
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Defect::class); return $this->respondWithResource(DefectResource::collection($this->service->paginate($request->validated())), 'Defects retrieved successfully.'); }
    public function store(DefectRequest $request) { return $this->respondSuccess(new DefectResource($this->service->create($request->validated())), 'Defect created successfully.', 201); }
    public function show(Defect $defect) { $this->authorize('view', $defect); return $this->respondSuccess(new DefectResource($defect), 'Defect retrieved successfully.'); }
    public function update(DefectRequest $request, Defect $defect) { return $this->respondSuccess(new DefectResource($this->service->update($defect, $request->validated())), 'Defect updated successfully.'); }
    public function destroy(Defect $defect) { $this->authorize('delete', $defect); $this->service->delete($defect); return $this->respondSuccess(null, 'Defect deleted successfully.'); }
}
