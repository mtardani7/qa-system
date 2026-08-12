<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'users.create' : 'users.update') ?? false; }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
        if ($this->has('is_qa_checker')) {
            $this->merge(['is_qa_checker' => filter_var($this->input('is_qa_checker'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)]);
        }
    }

    public function rules(): array
    {
        $id = $this->route('user')?->id;
        $passwordRules = $this->isMethod('post') ? ['required', 'confirmed', 'min:8'] : ['nullable', 'confirmed', 'min:8'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'employee_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_number')->ignore($id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => $passwordRules,
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'plant_ids' => ['array'],
            'plant_ids.*' => ['integer', Rule::exists('plants', 'id')],
            'is_active' => ['sometimes', 'boolean'],
            'is_qa_checker' => ['sometimes', 'boolean'],
        ];
    }
}
