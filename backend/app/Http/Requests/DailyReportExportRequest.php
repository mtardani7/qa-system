<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportExportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('daily-reports.export') ?? false; }
    public function rules(): array { return ['plant_id' => ['nullable', 'integer'], 'shift_id' => ['nullable', 'integer'], 'machine_id' => ['nullable', 'integer'], 'checker_id' => ['nullable', 'integer'], 'production_date_from' => ['nullable', 'date_format:Y-m-d'], 'production_date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:production_date_from']]; }
}
