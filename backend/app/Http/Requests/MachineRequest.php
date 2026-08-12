<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class MachineRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'machines.create' : 'machines.update') ?? false; }
    public function rules(): array { $id = $this->route('machine')?->id; $plantId = $this->integer('plant_id'); return ['plant_id' => ['required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)], 'line_id' => ['nullable', 'integer', Rule::exists('lines', 'id')->where(fn ($query) => $query->where('plant_id', $plantId)->where('is_active', true))], 'code' => ['required', 'string', 'max:40', Rule::unique('machines')->where('plant_id', $plantId)->ignore($id)], 'name' => ['required', 'string', 'max:150'], 'machine_number' => ['required', 'string', 'max:50'], 'description' => ['nullable', 'string', 'max:1000'], 'is_active' => ['sometimes', 'boolean']]; }
}
