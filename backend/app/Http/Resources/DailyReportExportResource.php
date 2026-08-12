<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyReportExportResource extends JsonResource
{
    public function toArray(Request $request): array { return ['id' => $this->id, 'status' => $this->status, 'file_path' => $this->when($this->status === 'completed', $this->file_path), 'error_message' => $this->when($this->status === 'failed', $this->error_message), 'created_at' => $this->created_at?->toISOString()]; }
}
