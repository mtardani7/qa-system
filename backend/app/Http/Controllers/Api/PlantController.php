<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PlantRequest;
use App\Http\Requests\MasterIndexRequest;
use App\Http\Resources\PlantResource;
use App\Models\Plant;
use App\Services\PlantService;
use Illuminate\Http\Request;

class PlantController extends BaseApiController
{
    public function __construct(private readonly PlantService $service) {}

    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', Plant::class); return $this->respondWithResource(PlantResource::collection($this->service->paginate($request->validated())), 'Plants retrieved successfully.'); }
    public function store(PlantRequest $request) { $plant = $this->service->create($request->validated()); return $this->respondSuccess(new PlantResource($plant), 'Plant created successfully.', 201); }
    public function show(Plant $plant) { $this->authorize('view', $plant); return $this->respondSuccess(new PlantResource($plant), 'Plant retrieved successfully.'); }
    public function update(PlantRequest $request, Plant $plant) { $this->authorize('update', $plant); return $this->respondSuccess(new PlantResource($this->service->update($plant, $request->validated())), 'Plant updated successfully.'); }
    public function destroy(Plant $plant) { $this->authorize('delete', $plant); $this->service->delete($plant); return $this->respondSuccess(null, 'Plant deleted successfully.'); }
}
