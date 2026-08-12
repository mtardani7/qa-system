<?php
namespace App\Http\Controllers\Api;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Requests\ShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use App\Services\ShiftService;
class ShiftController extends BaseApiController
{
    public function __construct(private readonly ShiftService $service) {}
    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Shift::class); return $this->respondWithResource(ShiftResource::collection($this->service->paginate($request->validated())), 'Shifts retrieved successfully.'); }
    public function store(ShiftRequest $request) { return $this->respondSuccess(new ShiftResource($this->service->create($request->validated())), 'Shift created successfully.', 201); }
    public function show(Shift $shift) { $this->authorize('view', $shift); return $this->respondSuccess(new ShiftResource($shift->load('plant')), 'Shift retrieved successfully.'); }
    public function update(ShiftRequest $request, Shift $shift) { return $this->respondSuccess(new ShiftResource($this->service->update($shift, $request->validated())), 'Shift updated successfully.'); }
    public function destroy(Shift $shift) { $this->authorize('delete', $shift); $this->service->delete($shift); return $this->respondSuccess(null, 'Shift deleted successfully.'); }
}
