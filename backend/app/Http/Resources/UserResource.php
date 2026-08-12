<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'employee_number' => $this->employee_number,
            'email' => $this->email,
            'roles' => $this->getRoleNames()->values()->all(),
            'plants' => $this->whenLoaded('plants', fn () => $this->plants->map(fn ($plant) => ['id' => $plant->id, 'code' => $plant->code, 'name' => $plant->name])->values()->all()),
            'is_active' => (bool) $this->is_active,
            'is_qa_checker' => (bool) $this->is_qa_checker,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
