<?php

namespace App\Http\Controllers\Api;

use App\Exports\DailyReportImportTemplateExport;
use App\Http\Requests\DailyReportImportRequest;
use App\Http\Resources\DailyReportImportResource;
use App\Jobs\ImportDailyReports;
use App\Models\DailyReportImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DailyReportImportController extends BaseApiController
{
	public function store(DailyReportImportRequest $request)
	{
		$path = $request->file('file')->store('imports/daily-reports');
		$import = DailyReportImport::create(['created_by' => $request->user()->id, 'plant_id' => $request->integer('plant_id'), 'line_id' => $request->integer('line_id') ?: null, 'file_name' => $request->file('file')->getClientOriginalName(), 'file_path' => $path, 'status' => 'queued']);
		ImportDailyReports::dispatch($import->id);
		return $this->respondSuccess(new DailyReportImportResource($import), 'Daily report import queued successfully.', 202);
	}

	public function template(Request $request)
	{
		$this->authorize('create', \App\Models\DailyReport::class);
		return Excel::download(new DailyReportImportTemplateExport(), 'daily-report-import-template.xlsx');
	}

	public function show(Request $request, DailyReportImport $import)
	{
		abort_unless($request->user()->can('daily-reports.import') || $request->user()->can('daily-reports.create') || $import->created_by === $request->user()->id, 403);
		return $this->respondSuccess(new DailyReportImportResource($import->fresh()), 'Import status retrieved successfully.');
	}
}
