<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Plant;
use App\Models\QaChecker;

class DailyReportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'daily-reports.create' : 'daily-reports.update') ?? false; }

    public function rules(): array
    {
        $plantId = $this->integer('plant_id');
        $legacyPayload = $this->has('defect_id') || !$this->has('defects');
        $allowedPlantIds = $this->allowedPlantIds();
        return [
            'plant_id' => ['required', 'integer', Rule::in($allowedPlantIds), Rule::exists('plants', 'id')->where('is_active', true)],
            'machine_id' => ['required', 'integer', Rule::exists('machines', 'id')->where(fn ($query) => $query->where('plant_id', $plantId)->where('is_active', true))],
            'shift_id' => ['required', 'integer', Rule::exists('shifts', 'id')->where(fn ($query) => $query->where('is_active', true)->where(function ($inner) use ($plantId) { $inner->where('plant_id', $plantId)->orWhereNull('plant_id'); }))],
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'product_type' => ['required', Rule::in(['FG', 'WIP'])],
            'checker_id' => [$legacyPayload ? 'nullable' : 'required', 'integer', Rule::exists('qa_checkers', 'id')->where(fn ($query) => $query->where('plant_id', $plantId)->where('is_active', true))],
            'checker_2_id' => ['nullable', 'integer', Rule::exists('qa_checkers', 'id')->where(fn ($query) => $query->where('plant_id', $plantId)->where('is_active', true)), 'different:checker_id'],
            'defects' => ['nullable', 'array'],
            'defects.*.defect_id' => ['required', 'integer', 'distinct', Rule::exists('defects', 'id')->where('is_active', true)],
            'defects.*.quantity' => ['required', 'integer', 'gt:0', 'max:1000000'],
            'defects.*.remarks' => ['nullable', 'string', 'max:1000'],
            'defect_id' => [$legacyPayload ? 'required' : 'nullable', 'integer', Rule::exists('defects', 'id')->where('is_active', true)],
            'quantity_defect' => [$legacyPayload ? 'required' : 'nullable', 'integer', 'gt:0', 'max:1000000'],
            'po_number' => ['required', 'string', 'max:100'],
            'output_box' => ['required', 'integer', 'gt:0', 'max:1000000'],
            'production_date' => ['required', 'date_format:Y-m-d'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'result' => ['nullable', Rule::in(['OK', 'OK, WITH NOTED', 'SORTIR', 'REJECT'])],
            'finding_range_box' => ['nullable', 'string', 'max:100'],
            'finding_observation' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', Rule::in(['Minor', 'Major', 'Critical'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $plantId = $this->integer('plant_id');
            $qaIds = array_filter([$this->integer('checker_id'), $this->integer('checker_2_id')]);
            if (!$plantId || !$qaIds) return;
            $validIds = QaChecker::query()->whereIn('id', $qaIds)->where('is_active', true)->where('plant_id', $plantId)->pluck('id')->all();
            foreach ($qaIds as $qaId) {
                if (!in_array($qaId, $validIds, true)) $validator->errors()->add('checker_id', 'Selected QA checker must be active and assigned to the selected plant.');
            }
        });
    }

    private function allowedPlantIds(): array
    {
        $user = $this->user();
        if ($user?->hasRole('Super Admin')) return Plant::query()->where('is_active', true)->pluck('id')->all();
        return $user?->plants()->where('plants.is_active', true)->pluck('plants.id')->all() ?? [];
    }
}
