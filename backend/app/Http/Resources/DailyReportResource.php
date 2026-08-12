<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plant' => ['id' => $this->plant?->id, 'code' => $this->plant?->code, 'name' => $this->plant?->name],
            'machine' => ['id' => $this->machine?->id, 'code' => $this->machine?->code, 'name' => $this->machine?->name],
            'shift' => ['id' => $this->shift?->id, 'code' => $this->shift?->code, 'name' => $this->shift?->name],
            'product' => ['id' => $this->product?->id, 'code' => $this->product?->code, 'name' => $this->product?->name, 'item_name' => $this->product?->name, 'mm_number' => $this->product?->mm_number, 'description' => $this->product?->name, 'qty_per_box' => $this->product?->qty_per_box],
            'product_type' => $this->product_type,
                'checker' => ['id' => $this->checker?->id ?? $this->qaChecker?->id, 'employee_number' => $this->checker?->employee_number, 'name' => $this->checker?->name ?? $this->qaChecker?->name, 'position' => $this->checker?->position],
                'checker_2' => $this->checker2 ? ['id' => $this->checker2->id, 'employee_number' => $this->checker2->employee_number, 'name' => $this->checker2->name] : null,
                'qa_checker_2' => $this->checker2 ? ['id' => $this->checker2->id, 'employee_number' => $this->checker2->employee_number, 'name' => $this->checker2->name] : null,
            'mm_number' => $this->mm_number,
            'po_number' => $this->po_number,
            'output_box' => $this->output_box,
            'qty_per_box' => $this->qty_per_box,
            'output_pcs' => $this->output_pcs,
            'defects' => $this->defects->map(fn ($item) => ['id' => $item->id, 'defect_id' => $item->defect_id, 'defect' => ['id' => $item->defect?->id, 'code' => $item->defect?->code, 'name' => $item->defect?->name, 'category' => $item->defect?->category], 'quantity' => $item->quantity, 'remarks' => $item->remarks])->values(),
            'total_defect' => $this->defects->sum('quantity'),
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
