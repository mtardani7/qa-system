<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MasterIndexRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
        $this->replace(array_filter($this->all(), static fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'plant_id' => ['nullable', 'integer'],
            'line_id' => ['nullable', 'integer'],
            'category' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'string', 'max:30'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
