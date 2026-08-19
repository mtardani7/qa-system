<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DashboardRequest;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;

class DashboardController extends BaseApiController
{
    public function __construct(private readonly DashboardService $service) {}
    public function overview(DashboardRequest $request) { return $this->respondSuccess(new DashboardResource($this->service->overview($request->filters())), 'Dashboard data retrieved successfully.'); }
}
