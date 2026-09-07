<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $snapshot = $this->master_snapshot ?? [];
        $plant = $snapshot['plant'] ?? $this->plant;
        $machine = $snapshot['machine'] ?? $this->machine;
        $shift = $snapshot['shift'] ?? $this->shift;
        $product = $snapshot['product'] ?? $this->product;
        $checker = $snapshot['checker'] ?? $this->checker;
        $checker2 = $snapshot['checker_2'] ?? $this->checker2;
        $defects = array_key_exists('defects', $snapshot) ? collect($snapshot['defects']) : $this->defects;
        return [
            'id' => $this->id,
            'plant' => ['id' => data_get($plant, 'id'), 'code' => data_get($plant, 'code'), 'name' => data_get($plant, 'name')],
            'machine' => ['id' => data_get($machine, 'id'), 'code' => data_get($machine, 'code'), 'name' => data_get($machine, 'name')],
            'shift' => ['id' => data_get($shift, 'id'), 'code' => data_get($shift, 'code'), 'name' => data_get($shift, 'name')],
            'product' => ['id' => data_get($product, 'id'), 'code' => data_get($product, 'code'), 'name' => data_get($product, 'name'), 'item_name' => data_get($product, 'name'), 'mm_number' => data_get($product, 'mm_number'), 'description' => data_get($product, 'description') ?: data_get($product, 'name'), 'qty_per_box' => data_get($product, 'qty_per_box')],
            'product_type' => $this->product_type,
                'checker' => ['id' => data_get($checker, 'id') ?? $this->qaChecker?->id, 'employee_number' => data_get($checker, 'employee_number'), 'name' => data_get($checker, 'name') ?? $this->qaChecker?->name, 'position' => data_get($checker, 'position')],
                'checker_2' => $checker2 ? ['id' => data_get($checker2, 'id'), 'employee_number' => data_get($checker2, 'employee_number'), 'name' => data_get($checker2, 'name')] : null,
                'qa_checker_2' => $checker2 ? ['id' => data_get($checker2, 'id'), 'employee_number' => data_get($checker2, 'employee_number'), 'name' => data_get($checker2, 'name')] : null,
            'mm_number' => $this->mm_number,
            'po_number' => $this->po_number,
            'output_box' => $this->output_box,
            'qty_per_box' => $this->qty_per_box,
            'output_pcs' => $this->output_pcs,
            'defects' => $defects->map(fn ($item) => ['id' => data_get($item, 'id'), 'defect_id' => data_get($item, 'defect_id'), 'defect' => ['id' => data_get($item, 'defect.id'), 'code' => data_get($item, 'defect.code'), 'name' => data_get($item, 'defect.name'), 'description' => data_get($item, 'defect.description'), 'category' => data_get($item, 'defect.category')], 'quantity' => data_get($item, 'quantity'), 'remarks' => data_get($item, 'remarks')])->values(),
            'total_defect' => $defects->sum('quantity'),
            'defect' => $this->defect ? ['id' => $this->defect->id, 'code' => $this->defect->code, 'name' => $this->defect->name, 'category' => $this->defect->category] : null,
            'quantity_defect' => $this->quantity_defect,
            'finding_range_box' => $this->finding_range_box,
            'finding_observation' => $this->finding_observation,
            'category' => $this->category,
            'qa_checker' => $this->checker ? ['id' => $this->checker->id, 'name' => $this->checker->name] : null,
            'production_date' => $this->production_date?->format('Y-m-d'),
            'remarks' => $this->remarks,
            'result' => $this->result,
            'status' => $this->status?->value,
            'audits' => $this->whenLoaded('audits', fn () => $this->audits->map(fn ($audit) => ['id' => $audit->id, 'action' => $audit->action, 'user' => $audit->user?->name, 'ip_address' => $audit->ip_address, 'old_value' => $audit->old_value, 'new_value' => $audit->new_value, 'created_at' => $audit->created_at?->toISOString()])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
