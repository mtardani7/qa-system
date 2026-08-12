<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class LineRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'lines.create' : 'lines.update') ?? false; }
    public function rules(): array { $id = $this->route('line')?->id; return ['plant_id' => ['required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)], 'code' => ['required', 'string', 'max:40', Rule::unique('lines')->where('plant_id', $this->integer('plant_id'))->ignore($id)], 'name' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['sometimes', 'boolean']]; }
}
