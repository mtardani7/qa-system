<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlantRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'plants.create' : 'plants.update') ?? false; }

    public function rules(): array
    {
        $plantId = $this->route('plant')?->id;
        return [
            'code' => ['required', 'string', 'max:30', Rule::unique('plants', 'code')->ignore($plantId)],
            'company_id' => ['nullable', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
