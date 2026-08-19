<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DailyReportExportRequest;
use App\Http\Resources\DailyReportExportResource;
use App\Models\DailyReportExport;
use App\Services\DailyReportExportService;
use Illuminate\Support\Facades\Storage;

class DailyReportExportController extends BaseApiController
{
    public function __construct(private readonly DailyReportExportService $service) {}
    public function store(DailyReportExportRequest $request) { return $this->respondSuccess(new DailyReportExportResource($this->service->dispatch($request->validated())), 'Daily report export queued successfully.', 202); }
    public function show(DailyReportExport $export) { $this->authorize('view', $export); return $this->respondSuccess(new DailyReportExportResource($export), 'Export status retrieved successfully.'); }
    public function download(int $export)
    {
        $record = DailyReportExport::query()->findOrFail($export);
        $this->authorize('view', $record);
        abort_unless($record->status === 'completed' && $record->file_path && Storage::exists($record->file_path), 404);
        return response(Storage::get($record->file_path), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="daily-reports-'.$record->id.'.xlsx"',
        ]);
        abort_unless($record->status === 'completed' && $record->file_path && Storage::exists($record->file_path), 404);
        return response(Storage::get($record->file_path), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="daily-reports-'.$record->id.'.xlsx"',
        ]);
    }
}
