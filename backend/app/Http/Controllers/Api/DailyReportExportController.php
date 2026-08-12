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
    public function download(DailyReportExport $export) { $this->authorize('view', $export); abort_unless($export->status === 'completed' && $export->file_path && Storage::exists($export->file_path), 404); return Storage::download($export->file_path, "daily-reports-{$export->id}.xlsx"); }
}
