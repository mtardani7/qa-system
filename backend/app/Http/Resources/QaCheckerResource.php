<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QaCheckerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'employee_number' => $this->employee_number, 'name' => $this->name, 'position' => $this->position, 'plant' => $this->whenLoaded('plant', fn () => ['id' => $this->plant->id, 'code' => $this->plant->code, 'name' => $this->plant->name]), 'plant_id' => $this->plant_id, 'is_active' => $this->is_active, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()];
    }
}
