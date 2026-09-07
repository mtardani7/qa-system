<?php

namespace App\Policies;

use App\Models\DailyReport;
use App\Models\User;
use App\Enums\DailyReportStatus;

class DailyReportPolicy
{
    public function viewAny(User $user): bool { return $user->can('daily-reports.view'); }
    public function view(User $user, DailyReport $report): bool { return $user->can('daily-reports.view') && $this->inScope($user, $report); }
    public function create(User $user): bool { return $user->can('daily-reports.create'); }
    public function update(User $user, DailyReport $report): bool { return $user->can('daily-reports.update') && $report->status === DailyReportStatus::Draft && $this->inScope($user, $report); }
    public function delete(User $user, DailyReport $report): bool { return $user->can('daily-reports.delete') && $report->status === DailyReportStatus::Draft && $this->inScope($user, $report); }
    public function lock(User $user, DailyReport $report): bool { return $user->can('daily-reports.lock') && $report->status === DailyReportStatus::Draft && $this->inScope($user, $report); }
    public function duplicate(User $user, DailyReport $report): bool { return $user->can('daily-reports.create'); }
    private function inScope(User $user, DailyReport $report): bool { return $user->hasRole('Super Admin') || $user->plants()->whereKey($report->plant_id)->exists(); }
}
