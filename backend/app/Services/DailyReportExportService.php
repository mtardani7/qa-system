<?php

namespace App\Services;

use App\Jobs\GenerateDailyReportExport;
use App\Models\DailyReportExport;
use Illuminate\Support\Facades\Auth;

final class DailyReportExportService
{
    public function dispatch(array $filters): DailyReportExport
    {
        $export = DailyReportExport::query()->create(['created_by' => Auth::id(), 'status' => 'queued', 'filters' => $filters]);
        $path = "exports/daily-reports-{$export->id}.xlsx";
        $export->update(['file_path' => $path, 'status' => 'processing']);
        GenerateDailyReportExport::dispatch($export->id, $filters, $path);
        return $export->fresh();
    }
}
