<?php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class MachineResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'plant' => ['id' => $this->plant?->id, 'code' => $this->plant?->code, 'name' => $this->plant?->name], 'line' => $this->line ? ['id' => $this->line->id, 'code' => $this->line->code, 'name' => $this->line->name] : null, 'plant_id' => $this->plant_id, 'line_id' => $this->line_id, 'code' => $this->code, 'name' => $this->name, 'machine_number' => $this->machine_number, 'description' => $this->description, 'is_active' => $this->is_active, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString()]; }
}
