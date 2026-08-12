<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'code' => $this->code, 'name' => $this->name, 'mm_number' => $this->mm_number, 'description' => $this->description, 'qty_per_box' => $this->qty_per_box, 'is_active' => $this->is_active, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]; }
}
