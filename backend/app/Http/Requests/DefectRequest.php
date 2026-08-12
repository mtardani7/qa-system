<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class DefectRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'defects.create' : 'defects.update') ?? false; }
    public function rules(): array { $id = $this->route('defect')?->id; return ['code' => ['required', 'string', 'max:50', Rule::unique('defects', 'code')->ignore($id)], 'name' => ['required', 'string', 'max:200'], 'category' => ['required', Rule::in(['CRITICAL', 'MAJOR', 'MINOR', 'UNACCEPTABLE', '-'])], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['sometimes', 'boolean']]; }
}
