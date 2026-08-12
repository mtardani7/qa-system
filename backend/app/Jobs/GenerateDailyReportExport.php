<?php

namespace App\Jobs;

use App\Exports\DailyReportsExport;
use App\Models\DailyReportExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class GenerateDailyReportExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly int $exportId, private readonly array $filters, private readonly string $path) {}

    public function handle(): void
    {
        try {
            Excel::store(new DailyReportsExport($this->filters), $this->path);
            DailyReportExport::query()->whereKey($this->exportId)->update(['status' => 'completed']);
        } catch (Throwable $exception) {
            DailyReportExport::query()->whereKey($this->exportId)->update(['status' => 'failed', 'error_message' => $exception->getMessage()]);
            throw $exception;
        }
    }
}
