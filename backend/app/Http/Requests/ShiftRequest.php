<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ShiftRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'shifts.create' : 'shifts.update') ?? false; }
    public function rules(): array { $id = $this->route('shift')?->id; return ['plant_id' => ['nullable', 'integer', Rule::exists('plants', 'id')->where('is_active', true)], 'code' => ['required', 'string', 'max:30', Rule::unique('shifts')->where('plant_id', $this->integer('plant_id'))->ignore($id)], 'name' => ['required', 'string', 'max:100'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i'], 'is_active' => ['sometimes', 'boolean']]; }
}
