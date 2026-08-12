<?php

namespace App\Jobs;

use App\Models\DailyReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkDailyReportExportCompleted implements ShouldQueue
{
    public function __construct(private readonly int $exportId) {}
    public function handle(): void { DailyReportExport::query()->whereKey($this->exportId)->update(['status' => 'completed']); }
}
