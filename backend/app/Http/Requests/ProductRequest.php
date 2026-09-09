<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class ProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can($this->isMethod('post') ? 'products.create' : 'products.update') ?? false; }
    public function rules(): array { $routeProduct = $this->route('product'); $id = $routeProduct instanceof \App\Models\Product ? $routeProduct->id : $routeProduct; $plantId = $this->integer('plant_id'); $legacyPayload = $this->isMethod('post') && $this->filled('name') && !$this->filled('mm_number') && !$this->filled('qty_per_box'); return ['plant_id' => [$legacyPayload ? 'nullable' : 'required', 'integer', Rule::exists('plants', 'id')->where('is_active', true)], 'code' => ['nullable', 'string', 'max:50', Rule::unique('products', 'code')->where('plant_id', $plantId)->ignore($id)], 'name' => [$legacyPayload ? 'required' : 'nullable', 'string', 'max:200', Rule::unique('products', 'name')->where('plant_id', $plantId)->ignore($id)], 'mm_number' => [$legacyPayload ? 'nullable' : 'required', 'string', 'max:100', Rule::unique('products', 'mm_number')->where('plant_id', $plantId)->ignore($id)], 'description' => ['nullable', 'string', 'max:1000'], 'qty_per_box' => [$legacyPayload ? 'nullable' : 'required', 'integer', 'min:1', 'max:1000000'], 'is_active' => ['sometimes', 'boolean']]; }
}
