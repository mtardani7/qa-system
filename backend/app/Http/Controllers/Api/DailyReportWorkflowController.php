<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Services\DailyReportWorkflowService;

class DailyReportWorkflowController extends BaseApiController
{
    public function __construct(private readonly DailyReportWorkflowService $workflow, private readonly DailyReportService $service) {}
    public function submit(DailyReport $dailyReport) { $this->authorize('submit', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'submit')), 'Daily report submitted successfully.'); }
    public function review(DailyReport $dailyReport) { $this->authorize('review', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'review')), 'Daily report reviewed successfully.'); }
    public function approve(DailyReport $dailyReport) { $this->authorize('approve', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'approve')), 'Daily report approved successfully.'); }
    public function reject(DailyReport $dailyReport) { $this->authorize('reject', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'reject')), 'Daily report rejected successfully.'); }
    public function lock(DailyReport $dailyReport) { $this->authorize('lock', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->workflow->transition($dailyReport, 'lock')), 'Daily report locked successfully.'); }
    public function duplicate(DailyReport $dailyReport) { $this->authorize('duplicate', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->service->duplicate($dailyReport)), 'Daily report duplicated successfully.', 201); }
}
