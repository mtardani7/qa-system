<?php

namespace App\Policies;

use App\Models\DailyReportExport;
use App\Models\User;

class DailyReportExportPolicy
{
    public function view(User $user, DailyReportExport $export): bool { return $user->can('daily-reports.export') && $export->created_by === $user->id; }
}
