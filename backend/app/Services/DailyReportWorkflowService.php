<?php

namespace App\Services;

use App\Enums\DailyReportStatus;
use App\Models\DailyReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DailyReportWorkflowService
{
    public function __construct(private readonly DailyReportAuditService $audits) {}

    public function transition(DailyReport $report, string $action): DailyReport
    {
        return DB::transaction(function () use ($report, $action): DailyReport {
            $report->refresh();
            $old = ['status' => $report->status?->value];
            $target = match ($action) {
                'submit' => $this->move($report, [DailyReportStatus::Draft], DailyReportStatus::Submitted),
                'review' => $this->move($report, [DailyReportStatus::Submitted], DailyReportStatus::Reviewed),
                'approve' => $this->move($report, [DailyReportStatus::Reviewed], DailyReportStatus::Reviewed),
                'reject' => $this->move($report, [DailyReportStatus::Submitted, DailyReportStatus::Reviewed], DailyReportStatus::Draft),
                'lock' => $this->move($report, [DailyReportStatus::Reviewed], DailyReportStatus::Locked),
                'cancel' => $this->move($report, [DailyReportStatus::Draft, DailyReportStatus::Submitted], DailyReportStatus::Cancelled),
                default => throw ValidationException::withMessages(['action' => 'Unsupported workflow action.']),
            };
            $report->save();
            $this->audits->record($report, $action, $old, ['status' => $report->status?->value]);
            return $report->fresh();
        });
    }

    private function move(DailyReport $report, array $allowed, DailyReportStatus $target): DailyReportStatus
    {
        if (!in_array($report->status, $allowed, true)) throw ValidationException::withMessages(['status' => "Cannot perform this action while the report is {$report->status?->value}."]);
        $report->status = $target;
        return $target;
    }
}
