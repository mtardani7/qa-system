<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Services\DailyReportWorkflowService;

class DailyReportWorkflowController extends BaseApiController
{
    public function __construct(private readonly DailyReportWorkflowService $workflow, private readonly DailyReportService $service) {}
    public function lock(DailyReport $dailyReport) { $this->authorize('lock', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'lock')), 'Daily report locked successfully.'); }
    public function duplicate(DailyReport $dailyReport) { $this->authorize('duplicate', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->service->duplicate($dailyReport)), 'Daily report duplicated successfully.', 201); }
}
