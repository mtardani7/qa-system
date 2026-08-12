<?php

namespace App\Http\Requests;

use App\DTO\DashboardFilters;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('dashboard.view') ?? false; }
    public function rules(): array { return ['date' => ['nullable', 'date_format:Y-m-d'], 'period' => ['nullable', 'in:daily,weekly,monthly,yearly'], 'plant_id' => ['nullable', 'integer'], 'line_id' => ['nullable', 'integer'], 'shift_id' => ['nullable', 'integer'], 'machine_id' => ['nullable', 'integer'], 'product_id' => ['nullable', 'integer'], 'checker_id' => ['nullable', 'integer']]; }
    public function filters(): DashboardFilters
    {
        $data = $this->validated(); $date = CarbonImmutable::parse($data['date'] ?? now()->toDateString()); $period = $data['period'] ?? 'daily';
        [$from, $to] = match ($period) { 'weekly' => [$date->startOfWeek(), $date->endOfWeek()], 'monthly' => [$date->startOfMonth(), $date->endOfMonth()], 'yearly' => [$date->startOfYear(), $date->endOfYear()], default => [$date, $date] };
        return new DashboardFilters($date, $data['plant_id'] ?? null, $data['shift_id'] ?? null, $data['machine_id'] ?? null, $data['line_id'] ?? null, $data['product_id'] ?? null, $data['checker_id'] ?? null, $from, $to, $period);
    }
}
