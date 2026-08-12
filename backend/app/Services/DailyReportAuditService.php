<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\DailyReportAudit;
use Illuminate\Support\Facades\Auth;

final class DailyReportAuditService
{
    public function record(?DailyReport $report, string $action, ?array $oldValue = null, ?array $newValue = null): DailyReportAudit
    {
        return DailyReportAudit::query()->create(['daily_report_id' => $report?->id, 'user_id' => Auth::id(), 'action' => $action, 'ip_address' => request()->ip(), 'old_value' => $oldValue, 'new_value' => $newValue]);
    }
}
