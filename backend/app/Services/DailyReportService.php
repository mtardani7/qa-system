<?php

namespace App\Services;

use App\Models\Product;
use App\Models\DailyReport;
use App\Enums\DailyReportStatus;
use App\Repositories\Contracts\DailyReportDefectRepositoryInterface;
use App\Repositories\Contracts\DailyReportRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class DailyReportService extends BaseService
{
    public function __construct(DailyReportRepositoryInterface $reports, private readonly DailyReportDefectRepositoryInterface $defects, private readonly DailyReportAuditService $audits, private readonly QualityNotificationService $notifications) { parent::__construct($reports); }

    public function create(array $attributes): Model
    {
        return DB::transaction(function () use ($attributes): Model {
            [$parent, $defectRows] = $this->prepare($attributes);
            $report = $this->repository->create($parent);
            $this->defects->createMany($report->id, $defectRows);
            $report = $this->repository->findOrFail($report->id);
            $this->audits->record($report, 'create', null, ['status' => $report->status?->value]);
            $this->notifications->evaluate($report);
            return $report;
        });
    }

    public function update(Model $model, array $attributes): Model
    {
        return DB::transaction(function () use ($model, $attributes): Model {
            $old = $model->only(['status', 'po_number', 'output_box', 'output_pcs', 'quantity_defect']);
            [$parent, $defectRows] = $this->prepare($attributes);
            $report = $this->repository->update($model, $parent);
            $this->defects->deleteForReport($report->id);
            $this->defects->createMany($report->id, $defectRows);
            $report = $this->repository->findOrFail($report->id);
            $this->audits->record($report, 'edit', $old, $report->only(['status', 'po_number', 'output_box', 'output_pcs', 'quantity_defect']));
            return $report;
        });
    }

    public function delete(Model $model): void
    {
        $this->deleteMany([$model]);
    }

    public function deleteMany(iterable $models): void
    {
        DB::transaction(function () use ($models): void {
            foreach ($models as $model) {
                $report = $model instanceof DailyReport ? $model : DailyReport::query()->findOrFail($model->getKey());
                $old = $report->only(['status', 'po_number', 'output_pcs']);
                $report->defects()->delete();
                $this->repository->delete($report);
                $this->audits->record(null, 'delete', $old, null);
            }
        });
    }

    public function duplicate(DailyReport $report): Model
    {
        $report->load('defects');
        return $this->create(['plant_id' => $report->plant_id, 'machine_id' => $report->machine_id, 'shift_id' => $report->shift_id, 'product_id' => $report->product_id, 'product_type' => $report->product_type, 'category' => $report->category, 'checker_id' => $report->checker_id, 'checker_2_id' => $report->checker_2_id, 'po_number' => $report->po_number, 'output_box' => $report->output_box, 'production_date' => $report->production_date?->format('Y-m-d'), 'remarks' => $report->remarks, 'result' => $report->result, 'finding_range_box' => $report->finding_range_box, 'finding_observation' => $report->finding_observation, 'defects' => $report->defects->map(fn ($defect) => ['defect_id' => $defect->defect_id, 'quantity' => $defect->quantity, 'remarks' => $defect->remarks])->all()]);
    }

    private function prepare(array $attributes): array
    {
        $product = Product::query()->whereKey($attributes['product_id'])->where('is_active', true)->lockForUpdate()->firstOrFail();
        $defectRows = array_key_exists('defects', $attributes)
            ? ($attributes['defects'] ?? [])
            : [['defect_id' => $attributes['defect_id'], 'quantity' => $attributes['quantity_defect']]];
        $attributes['mm_number'] = $product->mm_number;
        $attributes['qty_per_box'] = $product->qty_per_box;
        $attributes['output_pcs'] = (int) $attributes['output_box'] * (int) $product->qty_per_box;
        $attributes['quantity_defect'] = array_sum(array_column($defectRows, 'quantity'));
        $attributes['status'] ??= DailyReportStatus::Draft;
        if (!array_key_exists('defect_id', $attributes)) { $attributes['defect_id'] = null; }
        unset($attributes['defects']);
        return [$attributes, $defectRows];
    }
}
