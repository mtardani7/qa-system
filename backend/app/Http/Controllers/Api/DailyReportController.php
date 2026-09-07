<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\DailyReportRequest;
use App\Http\Resources\DailyReportResource;
use App\Models\DailyReport;
use App\Services\DailyReportService;
use App\Services\DailyReportWorkflowService;
use App\Enums\DailyReportStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class DailyReportController extends BaseApiController
{
    public function __construct(private readonly DailyReportService $service, private readonly DailyReportWorkflowService $workflow) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', DailyReport::class);
        if ($request->has('output_box_zero')) {
            $request->merge(['output_box_zero' => filter_var($request->input('output_box_zero'), FILTER_VALIDATE_BOOLEAN)]);
        }
        $filters = $request->validate([
            'period' => ['nullable', 'in:daily,weekly,monthly,yearly'], 'plant_id' => ['nullable', 'integer'], 'machine_id' => ['nullable', 'integer'], 'shift_id' => ['nullable', 'integer'], 'product_id' => ['nullable', 'integer'], 'product_type' => ['nullable', 'in:FG,WIP'], 'defect_id' => ['nullable', 'integer'], 'checker_id' => ['nullable', 'integer'], 'result' => ['nullable', Rule::in(['OK', 'OK, WITH NOTED', 'SORTIR', 'REJECT'])],
            'production_date' => ['nullable', 'date_format:Y-m-d'], 'production_date_from' => ['nullable', 'date_format:Y-m-d'], 'production_date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:production_date_from'],
            'search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:draft,locked'], 'output_box_zero' => ['nullable', 'boolean'], 'sort' => ['nullable', 'string', 'max:40'], 'direction' => ['nullable', 'in:asc,desc'], 'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        $filters['per_page'] ??= 50;
        if (!$request->user()->hasRole('Super Admin')) $filters['scope_plant_ids'] = $request->user()->plants()->where('plants.is_active', true)->pluck('plants.id')->all();
        return $this->respondWithResource(DailyReportResource::collection($this->service->paginate($filters)), 'Daily reports retrieved successfully.');
    }

    public function store(DailyReportRequest $request) { $this->authorize('create', DailyReport::class); return $this->respondSuccess(new DailyReportResource($this->service->create($request->validated())), 'Daily report created successfully.', 201); }
    public function show(DailyReport $dailyReport) { $this->authorize('view', $dailyReport); return $this->respondSuccess(new DailyReportResource($dailyReport->load(['plant', 'machine', 'shift', 'product', 'checker', 'checker2', 'qaChecker', 'defects.defect', 'audits.user'])), 'Daily report retrieved successfully.'); }
    public function print(DailyReport $dailyReport) { $this->authorize('view', $dailyReport); return view('daily-reports.print', ['report' => $dailyReport->load(['plant', 'line', 'machine', 'shift', 'product', 'checker', 'defects.defect'])]); }
    public function update(DailyReportRequest $request, DailyReport $dailyReport) { $this->authorize('update', $dailyReport); return $this->respondSuccess(new DailyReportResource($this->service->update($dailyReport, $request->validated())), 'Daily report updated successfully.'); }
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate(['ids' => ['required', 'array', 'min:1', 'max:100'], 'ids.*' => ['integer', 'distinct', 'exists:daily_reports,id']]);
        $reports = DailyReport::query()->whereIn('id', $validated['ids'])->get();
        foreach ($reports as $report) $this->authorize('delete', $report);
        $this->service->deleteMany($reports);
        return $this->respondSuccess(null, 'Daily reports deleted successfully.');
    }

    public function bulkLock(Request $request)
    {
        $validated = $request->validate(['month' => ['required', 'date_format:Y-m'], 'plant_id' => ['nullable', 'integer']]);
        $month = Carbon::createFromFormat('!Y-m', $validated['month'], config('app.timezone'));
        $today = Carbon::now(config('app.timezone'))->startOfDay();
        if ($today->lt($month->copy()->endOfMonth()) || $today->gt($month->copy()->addMonthNoOverflow()->endOfMonth())) {
            throw ValidationException::withMessages(['month' => 'A month can only be locked at month-end or during the following month.']);
        }
        $query = DailyReport::query()->whereBetween('production_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])->where('status', DailyReportStatus::Draft);
        if (!$request->user()->hasRole('Super Admin')) $query->whereIn('plant_id', $request->user()->plants()->where('plants.is_active', true)->pluck('plants.id'));
        $query->when($validated['plant_id'] ?? null, fn ($reports, $plantId) => $reports->where('plant_id', $plantId));
        $reports = $query->get();
        foreach ($reports as $report) $this->authorize('lock', $report);
        foreach ($reports as $report) $this->workflow->transition($report, 'lock');
        return $this->respondSuccess(['locked' => $reports->count(), 'month' => $validated['month']], 'Daily reports locked successfully.');
    }

    public function destroy(DailyReport $dailyReport) { $this->authorize('delete', $dailyReport); $this->service->delete($dailyReport); return $this->respondSuccess(null, 'Daily report deleted successfully.'); }
}
