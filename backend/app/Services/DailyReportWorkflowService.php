<?php

namespace App\Services;

use App\Enums\DailyReportStatus;
use App\Models\DailyReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

final class DailyReportWorkflowService
{
    public function __construct(private readonly DailyReportAuditService $audits) {}

    public function transition(DailyReport $report, string $action): DailyReport
    {
        return DB::transaction(function () use ($report, $action): DailyReport {
            $report->refresh();
            $old = ['status' => $report->status?->value];
            $target = $action === 'lock' ? $this->lock($report) : throw ValidationException::withMessages(['action' => 'Unsupported workflow action.']);
            $report->save();
            $this->audits->record($report, $action, $old, ['status' => $report->status?->value]);
            return $report->fresh()->load(['plant', 'machine', 'shift', 'product', 'checker', 'checker2', 'defects.defect']);
        });
    }

    private function lock(DailyReport $report): DailyReportStatus
    {
        if ($report->status !== DailyReportStatus::Draft) throw ValidationException::withMessages(['status' => "Cannot perform this action while the report is {$report->status?->value}."]);
        $productionDate = $report->production_date->copy()->startOfDay();
        $today = Carbon::now(config('app.timezone'))->startOfDay();
        if ($today->lt($productionDate->copy()->endOfMonth()) || $today->gt($productionDate->copy()->addMonthNoOverflow()->endOfMonth())) {
            throw ValidationException::withMessages(['production_date' => 'A daily report can only be locked at month-end or during the following month.']);
        }
        $report->load(['plant', 'machine', 'shift', 'product', 'checker', 'checker2', 'defects.defect']);
        $report->master_snapshot = [
            'plant' => $this->snapshot($report->plant), 'machine' => $this->snapshot($report->machine),
            'shift' => $this->snapshot($report->shift), 'product' => $this->snapshot($report->product),
            'checker' => $this->snapshot($report->checker), 'checker_2' => $this->snapshot($report->checker2),
            'defects' => $report->defects->map(fn ($item) => ['id' => $item->id, 'defect_id' => $item->defect_id, 'defect' => $this->snapshot($item->defect), 'quantity' => $item->quantity, 'remarks' => $item->remarks])->values()->all(),
        ];
        $report->status = DailyReportStatus::Locked;
        return DailyReportStatus::Locked;
    }

    private function snapshot($model): ?array
    {
        return $model?->only(['id', 'code', 'name', 'employee_number', 'position', 'description', 'category', 'mm_number', 'qty_per_box']);
    }
}
