<?php

namespace App\Jobs;

use App\Models\DailyReportExport;
use Illuminate\Contracts\Queue\ShouldQueue;

class MarkDailyReportExportFailed implements ShouldQueue
{
    public function __construct(private readonly int $exportId) {}
    public function handle(?\Throwable $exception = null): void { DailyReportExport::query()->whereKey($this->exportId)->update(['status' => 'failed', 'error_message' => $exception?->getMessage()]); }
}
