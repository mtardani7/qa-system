<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QaCheckerRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'qa-checkers.create' : 'qa-checkers.update') ?? false; }

    public function rules(): array
    {
        $id = $this->route('qa_checker')?->id;
        return [
            'employee_number' => ['required', 'string', 'max:50', Rule::unique('qa_checkers', 'employee_number')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:100'],
            'plant_id' => ['required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
