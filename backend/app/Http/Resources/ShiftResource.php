<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'plant' => ['id' => $this->plant?->id, 'code' => $this->plant?->code, 'name' => $this->plant?->name], 'code' => $this->code, 'name' => $this->name, 'start_time' => $this->start_time?->format('H:i'), 'end_time' => $this->end_time?->format('H:i'), 'is_active' => $this->is_active, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]; }
}
