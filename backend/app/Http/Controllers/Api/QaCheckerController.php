<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MasterIndexRequest;
use App\Http\Requests\QaCheckerRequest;
use App\Http\Resources\QaCheckerResource;
use App\Models\QaChecker;
use App\Services\QaCheckerService;

class QaCheckerController extends BaseApiController
{
    public function __construct(private readonly QaCheckerService $service) {}

    public function index(MasterIndexRequest $request) { $this->authorize('viewAny', QaChecker::class); return $this->respondWithResource(QaCheckerResource::collection($this->service->paginate($request->validated())), 'QA checkers retrieved successfully.'); }
    public function store(QaCheckerRequest $request) { $this->authorize('create', QaChecker::class); return $this->respondSuccess(new QaCheckerResource($this->service->create($request->validated())->load('plant')), 'QA checker created successfully.', 201); }
    public function show(QaChecker $qaChecker) { $this->authorize('view', $qaChecker); return $this->respondSuccess(new QaCheckerResource($qaChecker->load('plant')), 'QA checker retrieved successfully.'); }
    public function update(QaCheckerRequest $request, QaChecker $qaChecker) { $this->authorize('update', $qaChecker); return $this->respondSuccess(new QaCheckerResource($this->service->update($qaChecker, $request->validated())->load('plant')), 'QA checker updated successfully.'); }
    public function destroy(QaChecker $qaChecker) { $this->authorize('delete', $qaChecker); $this->service->delete($qaChecker); return $this->respondSuccess(null, 'QA checker deleted successfully.'); }
}
