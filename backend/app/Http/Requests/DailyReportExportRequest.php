<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyReportExportRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('daily-reports.export') ?? false; }
    public function rules(): array { return ['period' => ['nullable', 'in:daily,weekly,monthly,yearly'], 'plant_id' => ['nullable', 'integer'], 'shift_id' => ['nullable', 'integer'], 'machine_id' => ['nullable', 'integer'], 'product_type' => ['nullable', 'in:FG,WIP'], 'checker_id' => ['nullable', 'integer'], 'result' => ['nullable', 'in:OK,"OK, WITH NOTED",SORTIR,REJECT'], 'status' => ['nullable', 'in:draft,locked'], 'output_box_zero' => ['nullable', 'boolean'], 'search' => ['nullable', 'string', 'max:100'], 'production_date_from' => ['nullable', 'date_format:Y-m-d'], 'production_date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:production_date_from']]; }
}
